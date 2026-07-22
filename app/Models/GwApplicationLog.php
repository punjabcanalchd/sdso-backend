<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Yungts97\LaravelUserActivityLog\Traits\Loggable;
use App\Models\GwApplication;

/**
 * Groundwater Application Logs
 *
 * @mixin Builder
 */
class GwApplicationLog extends Model
{
    use HasFactory;
    //use Loggable;

    /**
     * The primary key associated with the table.
     */
    protected $primaryKey = 'log_id';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'application_id',
        'unit_id',
        'service_type',
        'application_no',
        'fee_charges',
        'payment_status',
        'permission_no',
        'application_status',
        'form_no',
        'delete_status',
        'archived',
        'form_status',
        'application_date',
        'permission_date',
        'valid_upto',
        'rejection_date',
        'amd_permission_date',
        'actual_app_fee',
        'monthly_reading',
        'appealed_status',
        'adjusted_amount',
        'locked_profile',
        'locked_unit',
        'deemed_aproval',
        'auto_reject',
    ];

    /**
     * Attribute casting.
     */
    protected $casts = [
        'application_status' => 'integer',
        'form_no' => 'integer',
        'payment_status' => 'integer',
        'locked_profile' => 'integer',
        'locked_unit' => 'integer',
        'appealed_status' => 'integer',
        'adjusted_amount' => 'integer',

        'delete_status' => 'boolean',
        'archived' => 'boolean',
        'monthly_reading' => 'boolean',

        'fee_charges' => 'decimal:2',
        'actual_app_fee' => 'decimal:2',

        'application_date' => 'datetime',
        'permission_date' => 'datetime',
        'valid_upto' => 'datetime',
        'rejection_date' => 'datetime',
        'amd_permission_date' => 'datetime',

        'deemed_aproval' => 'datetime',
        'auto_reject' => 'datetime',
    ];

    /**
     * Application relationship.
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(
            GwApplication::class,
            'application_id',
            'application_id'
        );
    }

    /**
     * Get latest log id by application id or application number.
     */
    public static function getLogId(
        ?int $applicationId = null,
        ?string $applicationNo = null
    ): ?int {
        return self::query()
            ->when(
                $applicationId,
                fn ($query) => $query->where('application_id', $applicationId)
            )
            ->when(
                $applicationNo,
                fn ($query) => $query->orWhere('application_no', $applicationNo)
            )
            ->latest('log_id')
            ->value('log_id');
    }
}