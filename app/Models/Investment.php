<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Investment extends Model
{
    use HasFactory;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'date',
        'institution',
        'purpose',
        'amount',
        'details',
        'ref',
        'attachment_path',
        'attachment_name',
        'auto_renew',
        'term_months',
        'maturity_date',
        'status',
        'submitted_by',
        'approved_by',
        'approval_date',
        'historical',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'auto_renew' => 'boolean',
        'historical' => 'boolean',
        'date' => 'date',
        'maturity_date' => 'date',
        'approval_date' => 'date',
    ];
}
