<?php

namespace Tests\Feature;

use App\Enums\ApprovalStageStatus;
use App\Enums\UserRole;
use App\Models\Department;
use App\Models\LoaAccessToken;
use App\Models\LoaRequest;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ApprovalPipelineTest extends TestCase
{
    use RefreshDatabase;

    private Department $dcs;
    private Department $dte;
    private Program $bsit;
    private Program $beed;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();

        $this->dcs = Department::query()->where('code', 'DCS')->firstOrFail();
        $this->dte = Department::query()->where('code', 'DTE')->firstOrFail();
        $this->bsit = Program::query()->where('code', 'BSIT')->firstOrFail();
        $this->beed = Program::query()->where('code', 'BEED')->firstOrFail();
    }

    /**
     * AC-3: Pipeline page renders with correct 7 column headers.
     */
    public function test_pipeline_page_renders_table_headers_and_stage_badges(): void
    {
        // 1 LOA: DH approved, SASO pending (via model simulate)
        $dhApprovedLoa = $this->createSubmittedLoa($this->dcs, $this->bsit);
        $dhApprovedLoa->forceFill([
            'dept_head_status' => ApprovalStageStatus::Approved,
            'dept_head_at' => now(),
            'saso_status' => ApprovalStageStatus::Pending,
        ])->save();

        // 1 LOA: all pending (default)
        $allPendingLoa = $this->createSubmittedLoa($this->dcs, $this->bsit);

        $saso = User::factory()->create(['role' => UserRole::SasoOfficer]);

        $this->actingAs($saso)
            ->get(route('staff.pipeline'))
            ->assertOk()
            // Sidebar items present (staff shell)
            ->assertSeeText('Dashboard')
            ->assertSeeText('Approval Pipeline')
            ->assertSeeText('Reports & Analytics')
            ->assertSeeText('User Profile')
            ->assertSeeText('Logout')
            ->assertDontSeeText('Request LOA')
            // Table headers
            ->assertSeeText('App ID')
            ->assertSeeText('Student ID')
            ->assertSeeText('Student Name')
            ->assertSeeText('Dept Head Status')
            ->assertSeeText('SASO Status')
            ->assertSeeText('Campus Director Status')
            ->assertSeeText('Registrar Status')
            ->assertSeeText('Guidance Status')
            ->assertSeeText('Action Options')
            // Stage badges
            ->assertSeeText('Approved')
            ->assertSeeText('Pending');
    }

    /**
     * AC-4: DH can approve own-dept pending LOA → stage advances, saso becomes pending.
     */
    public function test_dh_approve_advances_saso_to_pending(): void
    {
        $loa = $this->createSubmittedLoa($this->dcs, $this->bsit);
        $this->assertSame('pending', $loa->fresh()->dept_head_status->value);
        $this->assertNull($loa->fresh()->saso_status);
        $this->assertSame('submitted', $loa->fresh()->status);

        $dhDcs = User::factory()->create([
            'role' => UserRole::DepartmentHead,
            'department_id' => $this->dcs->id,
        ]);

        $this->actingAs($dhDcs)
            ->post(route('staff.loa.approve', $loa))
            ->assertRedirect(route('staff.pipeline'))
            ->assertSessionHas('status');

        $fresh = $loa->fresh();
        $this->assertTrue($fresh->dept_head_status->is(ApprovalStageStatus::Approved));
        $this->assertNotNull($fresh->dept_head_at);
        $this->assertSame($dhDcs->id, $fresh->dept_head_by);
        $this->assertTrue($fresh->saso_status->is(ApprovalStageStatus::Pending));
        // Overall status stays submitted (still in pipeline)
        $this->assertSame('submitted', $fresh->status);
    }

    /**
     * AC-4: SASO approve fails when DH stage still pending (sequential enforcement).
     */
    public function test_saso_approve_fails_403_when_dh_pending(): void
    {
        $loa = $this->createSubmittedLoa($this->dcs, $this->bsit);
        // DH still pending
        $this->assertTrue($loa->dept_head_status->is(ApprovalStageStatus::Pending));

        $saso = User::factory()->create(['role' => UserRole::SasoOfficer]);

        $this->actingAs($saso)
            ->post(route('staff.loa.approve', $loa))
            ->assertForbidden();

        // No DB changes
        $fresh = $loa->fresh();
        $this->assertNull($fresh->saso_status);
        $this->assertTrue($fresh->dept_head_status->is(ApprovalStageStatus::Pending));
    }

    /**
     * AC-4: CD approve fails when SASO not done.
     */
    public function test_cd_approve_fails_403_when_saso_still_pending(): void
    {
        $loa = $this->createSubmittedLoa($this->dcs, $this->bsit);
        // Move to DH-approved SASO-pending stage first
        $loa->forceFill([
            'dept_head_status' => ApprovalStageStatus::Approved,
            'dept_head_at' => now(),
            'saso_status' => ApprovalStageStatus::Pending,
        ])->save();

        $cd = User::factory()->create(['role' => UserRole::CampusDirector]);

        // SASO still pending -> CD cannot act
        $this->actingAs($cd)
            ->post(route('staff.loa.approve', $loa))
            ->assertForbidden();

        $this->assertNull($loa->fresh()->campus_director_status);
    }

    /**
     * AC-5: SASO reject short-circuits pipeline → overall 'rejected', CD stage never reached.
     */
    public function test_saso_reject_shortcircuits_overall_rejected_cd_null(): void
    {
        $loa = $this->createSubmittedLoa($this->dcs, $this->bsit);
        // DH done, SASO pending
        $dhDcs = User::factory()->create(['role' => UserRole::DepartmentHead, 'department_id' => $this->dcs->id]);
        $loa->markApprovedBy($dhDcs);
        $this->assertTrue($loa->fresh()->saso_status->is(ApprovalStageStatus::Pending));

        $saso = User::factory()->create(['role' => UserRole::SasoOfficer]);

        $this->actingAs($saso)
            ->post(route('staff.loa.reject', $loa), ['reason' => 'Missing supporting docs.'])
            ->assertRedirect(route('staff.pipeline'))
            ->assertSessionHas('status');

        $fresh = $loa->fresh();
        $this->assertTrue($fresh->saso_status->is(ApprovalStageStatus::Rejected));
        $this->assertSame($saso->id, $fresh->saso_by);
        $this->assertNotNull($fresh->saso_at);
        $this->assertSame('rejected', $fresh->status);
        $this->assertSame($saso->id, $fresh->rejected_by);
        $this->assertNotNull($fresh->rejected_at);
        $this->assertSame('Missing supporting docs.', $fresh->rejection_reason);
        // CD stage NEVER reached (still null)
        $this->assertNull($fresh->campus_director_status);
    }

    /**
     * AC-6: CD approve → advances to Registrar stage (not fully approved yet).
     */
    public function test_cd_approve_advances_to_registrar(): void
    {
        $loa = $this->createSubmittedLoa($this->dcs, $this->bsit);

        $dhDcs = User::factory()->create(['role' => UserRole::DepartmentHead, 'department_id' => $this->dcs->id]);
        $saso  = User::factory()->create(['role' => UserRole::SasoOfficer]);
        $cd    = User::factory()->create(['role' => UserRole::CampusDirector]);

        $loa->markApprovedBy($dhDcs);
        $loa->markApprovedBy($saso);
        $this->assertTrue($loa->fresh()->campus_director_status->is(ApprovalStageStatus::Pending));

        $this->actingAs($cd)
            ->post(route('staff.loa.approve', $loa))
            ->assertRedirect(route('staff.pipeline'))
            ->assertSessionHas('status');

        $fresh = $loa->fresh();
        $this->assertTrue($fresh->campus_director_status->is(ApprovalStageStatus::Approved));
        $this->assertSame($cd->id, $fresh->campus_director_by);
        // Registrar is now pending — not fully approved yet
        $this->assertTrue($fresh->registrar_status->is(ApprovalStageStatus::Pending));
        $this->assertSame('submitted', $fresh->status);
    }

    /**
     * AC-6b: All 5 stages approved → overall status becomes 'approved'.
     */
    public function test_all_five_stages_approve_sets_overall_approved(): void
    {
        $loa = $this->createSubmittedLoa($this->dcs, $this->bsit);

        $dh       = User::factory()->create(['role' => UserRole::DepartmentHead, 'department_id' => $this->dcs->id]);
        $saso     = User::factory()->create(['role' => UserRole::SasoOfficer]);
        $cd       = User::factory()->create(['role' => UserRole::CampusDirector]);
        $reg      = User::factory()->create(['role' => UserRole::Registrar]);
        $guidance = User::factory()->create(['role' => UserRole::Guidance]);

        $loa->markApprovedBy($dh);
        $loa->markApprovedBy($saso);
        $loa->markApprovedBy($cd);
        $this->assertTrue($loa->fresh()->registrar_status->is(ApprovalStageStatus::Pending));

        $loa->markApprovedBy($reg);
        $this->assertTrue($loa->fresh()->guidance_status->is(ApprovalStageStatus::Pending));
        $this->assertSame('submitted', $loa->fresh()->status);

        $this->actingAs($guidance)
            ->post(route('staff.loa.approve', $loa))
            ->assertRedirect(route('staff.pipeline'));

        $fresh = $loa->fresh();
        $this->assertTrue($fresh->guidance_status->is(ApprovalStageStatus::Approved));
        $this->assertSame('approved', $fresh->status);
    }

    /**
     * AC-8: Registrar can only act at stage 4 — sees no buttons when earlier stages pending.
     * Guidance can only act at stage 5 — sees no buttons when earlier stages pending.
     */
    public function test_registrar_and_guidance_cannot_act_before_their_stage(): void
    {
        $loa = $this->createSubmittedLoa($this->dcs, $this->bsit);
        // Only DH approved — SASO pending. Registrar/Guidance should not see action buttons.
        $dhDcs = User::factory()->create(['role' => UserRole::DepartmentHead, 'department_id' => $this->dcs->id]);
        $loa->markApprovedBy($dhDcs);

        foreach ([UserRole::Registrar, UserRole::Guidance] as $role) {
            $user = User::factory()->create(['role' => $role]);
            $this->actingAs($user)
                ->get(route('staff.pipeline'))
                ->assertOk()
                ->assertSeeText('View')
                ->assertDontSeeText('Approve LOA')
                ->assertDontSeeText('Reject LOA');
        }
    }

    /**
     * AC-8b: Registrar sees Approve/Reject when it is their turn (stage 4).
     */
    public function test_registrar_sees_action_buttons_at_stage_4(): void
    {
        $loa = $this->createSubmittedLoa($this->dcs, $this->bsit);

        $dh   = User::factory()->create(['role' => UserRole::DepartmentHead, 'department_id' => $this->dcs->id]);
        $saso = User::factory()->create(['role' => UserRole::SasoOfficer]);
        $cd   = User::factory()->create(['role' => UserRole::CampusDirector]);

        $loa->markApprovedBy($dh);
        $loa->markApprovedBy($saso);
        $loa->markApprovedBy($cd);
        $this->assertTrue($loa->fresh()->registrar_status->is(ApprovalStageStatus::Pending));

        $registrar = User::factory()->create(['role' => UserRole::Registrar]);
        $this->actingAs($registrar)
            ->get(route('staff.pipeline'))
            ->assertOk()
            ->assertSeeText('Approve LOA');
    }

    /**
     * AC-7: Cross-dept DH POST approve → 403 from middleware (no DB change).
     */
    public function test_cross_dept_dh_post_approve_returns_403_and_no_db_change(): void
    {
        $dteLoa = $this->createSubmittedLoa($this->dte, $this->beed);

        $dcsDh = User::factory()->create([
            'role' => UserRole::DepartmentHead,
            'department_id' => $this->dcs->id,
        ]);

        $this->actingAs($dcsDh)
            ->post(route('staff.loa.approve', $dteLoa))
            ->assertForbidden();

        $fresh = $dteLoa->fresh();
        // Still pending, no changes
        $this->assertTrue($fresh->dept_head_status->is(ApprovalStageStatus::Pending));
        $this->assertNull($fresh->dept_head_by);
    }

    /**
     * Reports page skeleton sections present (AC-9).
     */
    public function test_reports_page_renders_filters_stats_charts(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Administrator]);
        $this->seedLoas(3, 2);

        $this->actingAs($admin)
            ->get(route('staff.reports'))
            ->assertOk()
            // Sidebar (staff shell)
            ->assertSeeText('Dashboard')
            ->assertSeeText('Approval Pipeline')
            ->assertSeeText('Reports & Analytics')
            ->assertSeeText('User Profile')
            ->assertSeeText('Logout')
            ->assertDontSeeText('Request LOA')
            // Filter labels
            ->assertSeeText('Year')
            ->assertSeeText('Department')
            ->assertSeeText('Status')
            ->assertSeeText('Apply')
            // No "future release" text
            ->assertDontSeeText('future release')
            // Stat card headings
            ->assertSeeText('LOA Count')
            ->assertSeeText('Processing Time')
            ->assertSeeText('Highest Program')
            // Chart section labels
            ->assertSeeText('Volume by Department')
            ->assertSeeText('Monthly Submissions')
            ->assertSeeText('Status Distribution')
            ->assertSeeText('Top Programs by Submission Volume');
    }

    /**
     * Profile page shows basic user info.
     */
    public function test_profile_page_shows_user_info(): void
    {
        $user = User::factory()->create([
            'name' => 'Maria Santos',
            'email' => 'maria@evsu.edu.ph',
            'role' => UserRole::Registrar,
        ]);

        $this->actingAs($user)
            ->get(route('staff.profile'))
            ->assertOk()
            ->assertSeeText('Maria Santos')
            ->assertSeeText('maria@evsu.edu.ph')
            ->assertSeeText('Registrar')
            ->assertSeeText('Edit Name')
            ->assertSeeText('Change Password')
            ->assertDontSeeText('Account actions coming soon')
            ->assertDontSeeText('future release');
    }

    /**
     * Double-approve prevented by policy (policy refuses second approve).
     */
    public function test_double_approve_prevented(): void
    {
        $loa = $this->createSubmittedLoa($this->dcs, $this->bsit);
        $dhDcs = User::factory()->create(['role' => UserRole::DepartmentHead, 'department_id' => $this->dcs->id]);

        // First approve → OK
        $this->actingAs($dhDcs)->post(route('staff.loa.approve', $loa))->assertRedirect();
        $this->assertTrue($loa->fresh()->dept_head_status->is(ApprovalStageStatus::Approved));

        // Second approve at same stage → 403
        $this->actingAs($dhDcs)->post(route('staff.loa.approve', $loa))->assertForbidden();
    }

    // ===============================
    // Helpers
    // ===============================

    private function createSubmittedLoa(Department $dept, Program $prog): LoaRequest
    {
        $token = LoaAccessToken::create([
            'student_id'   => fake()->numerify('####-####'),
            'full_name'    => fake()->name(),
            'email'        => fake()->safeEmail(),
            'token_hash'   => hash('sha256', Str::random(40)),
            'expires_at'   => now()->addHours(24),
        ]);

        $loa = LoaRequest::create([
            'loa_access_token_id' => $token->id,
            'student_id'          => $token->student_id,
            'full_name'           => $token->full_name,
            'email'               => $token->email,
            'department_id'       => $dept->id,
            'program_id'          => $prog->id,
            'year_level'          => $prog->code . '-1st',
            'start_date'          => now()->addDays(5)->toDateString(),
            'return_date'         => now()->addDays(60)->toDateString(),
            'reason'              => fake()->paragraph(),
            'parent_full_name'    => fake()->name(),
            'parent_relationship' => 'Parent',
            'parent_phone'        => '+639171234567',
            'status'              => 'submitted',
            'submitted_at'        => now(),
            'dept_head_status'    => ApprovalStageStatus::Pending,
        ]);

        $loa->assignControlNumber();
        $loa->save();

        return $loa->fresh();
    }

    private function seedLoas(int $dcsCount, int $dteCount): void
    {
        foreach (range(1, $dcsCount) as $i) {
            $this->createSubmittedLoa($this->dcs, $this->bsit);
        }
        foreach (range(1, $dteCount) as $i) {
            $this->createSubmittedLoa($this->dte, $this->beed);
        }
    }
}
