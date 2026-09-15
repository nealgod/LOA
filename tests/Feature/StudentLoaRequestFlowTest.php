<?php

namespace Tests\Feature;

use App\Mail\LoaFormAccessMail;
use App\Models\Department;
use App\Models\LoaRequest;
use App\Models\Program;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StudentLoaRequestFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_renders(): void
    {
        $this->get('/')->assertOk()->assertSee('Request LOA')->assertSee('Staff login');
    }

    public function test_identity_sends_form_link_and_submission_creates_control_number(): void
    {
        Mail::fake();
        Storage::fake('local');

        $this->seed();

        $department = Department::query()->where('code', 'DCS')->firstOrFail();
        $program = Program::query()->where('code', 'BSIT')->firstOrFail();

        $this->post('/loa/request', [
            'student_id' => '2021-0001',
            'full_name' => 'Juan Dela Cruz',
            'email' => 'juan@evsu.edu.ph',
        ])->assertRedirect(route('student.identity.sent'));

        Mail::assertSent(LoaFormAccessMail::class);

        $mail = Mail::sent(LoaFormAccessMail::class)->first();
        $url = $mail->formUrl;
        $token = basename(parse_url($url, PHP_URL_PATH));

        $this->get(route('student.form.show', $token))
            ->assertOk()
            ->assertSee('EVSU-SASO-F-040')
            ->assertSee('Department of Computer Studies');

        $this->post(route('student.form.store', $token), [
            'department_id' => $department->id,
            'program_id' => $program->id,
            'start_date' => '2026-10-01',
            'return_date' => '2027-03-01',
            'reason' => 'Medical treatment requiring extended recovery.',
            'parent_full_name' => 'Maria Dela Cruz',
            'parent_relationship' => 'Mother',
            'parent_phone' => '09171234567',
            'attachments' => [
                UploadedFile::fake()->create('medical.pdf', 120, 'application/pdf'),
                UploadedFile::fake()->image('id.jpg'),
            ],
        ])->assertRedirect(route('student.form.submitted', $token));

        $this->assertDatabaseHas('loa_requests', [
            'student_id' => '2021-0001',
            'status' => 'submitted',
            'department_id' => $department->id,
            'program_id' => $program->id,
        ]);

        $this->assertSame(2, LoaRequest::query()->first()->attachments()->count());
        $this->assertStringStartsWith('EVSU-OR-LOA-2026-', LoaRequest::query()->first()->control_number);
    }
}
