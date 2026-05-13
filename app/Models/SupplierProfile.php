<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierProfile extends Model
{
    protected $fillable = [
        'user_id',
        'company_name',
        'nit',
        'contact_name',
        'business_phone',
        'business_email',
        'business_address',
        'city',
        'bank_name',
        'account_type',
        'account_number',
        'credit_days',
    ];

    protected $casts = [
        'credit_days' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
