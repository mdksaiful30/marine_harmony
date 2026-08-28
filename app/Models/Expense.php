<?php

namespace App\Models;

use HasinHayder\TyroDashboard\Concerns\HasCrud;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasCrud, HasFactory;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'date',
        'category',
        'description',
        'amount',
        'details',
        'ref',
        'status',
        'submitted_by',
        'approved_by',
        'approval_date',
        'historical',
        'historical_type',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'historical' => 'boolean',
        'date' => 'date',
        'approval_date' => 'date',
    ];
}
