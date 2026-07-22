<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $table = 'payments';

    protected $primaryKey = 'id';

    protected $fillable = [
        'product',
        'user_id',
        'application_id',
        'application_type',
        'service_type',
        'firstname',
        'email',
        'phone_no',
        'payment_source',
        'post_response_string',
        'sent_amount',
        'received_amount',
        'returned_status',
        'mode',
        'mihpayid',
        'status',
        'txnid',
        'cin',
        'bank_ref_no',
        'fee_structure',
        'verification_param_string',
        'tracking_id',
        'sent_request_string',
        'deptRefNo',
        'verify_required_param',
        'last_fee_adjusted',
        'penalty_charges',
        'draft',
        'txn_initiated_date',
    ];

    protected $casts = [
        'sent_amount' => 'decimal:2',
        'received_amount' => 'decimal:2',
        'draft' => 'boolean',
        'txn_initiated_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * User relationship.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}