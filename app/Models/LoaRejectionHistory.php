<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoaRejectionHistory extends Model
{
    protected $table = 'loa_rejection_history';

    protected $fillable = [
        'loa_request_id',
        'stage_key',
        'stage_label',
        'rejected_by',
        'rejected_at',
        'reason',
        'resubmit_cycle',
    ];

    protected function casts(): array
    {
        return [
            'rejected_at' => 'datetime',
        ];
    }

    public function loaRequest(): BelongsTo
    {
        return $this->belongsTo(LoaRequest::class);
    }

    public function rejectedByActor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }
}
