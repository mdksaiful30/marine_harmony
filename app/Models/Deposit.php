<?php

namespace App\Models;

use HasinHayder\TyroDashboard\Concerns\HasCrud;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deposit extends Model
{
    use HasCrud, HasFactory;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'member',
        'date',
        'period',
        'method',
        'amount',
        'bank_name',
        'branch',
        'bank_ref',
        'tx_type',
        'mobile_wallet',
        'mobile_number',
        'mobile_ref',
        'receiver_name',
        'cash_location',
        'special',
        'remarks',
        'attachment_path',
        'attachment_name',
        'status',
        'submitted_by',
        'approved_by',
        'approval_date',
        'rejection_reason',
        'historical',
        'historical_type',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'historical' => 'boolean',
        'date' => 'date',
        'approval_date' => 'date',
    ];

    public function getPeriodsListAttribute(): array
    {
        if (empty($this->period)) {
            return [];
        }
        $val = trim($this->period);
        if (str_contains($val, ',')) {
            return array_map('trim', explode(',', $val));
        }

        return [$val];
    }
}
