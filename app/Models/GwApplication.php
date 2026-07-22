<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\GwApplicationService;
use App\Models\GwApplicationLog;
use App\Enums\ServiceType;
use App\Enums\ApplicationStatus;
use App\Models\Payment;

class GwApplication extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'gw_applications';

    /**
     * The primary key associated with the table.
     */
    protected $primaryKey = 'application_id';

    /**
     * Indicates if the IDs are auto-incrementing.
     */
    public $incrementing = true;

    /**
     * The data type of the primary key.
     */
    protected $keyType = 'int';

    /**
     * The attributes that are mass assignable.
     */
    protected $guarded = [];

    /**
     * Attribute casting.
     */
    protected $casts = [
        'application_status' => 'integer',
        'form_no' => 'integer',
        'delete_status' => 'boolean',
        'archived' => 'boolean',
        'fee_charges' => 'decimal:2',
        'payment_status' => 'integer',
        'locked_profile' => 'integer',
        'locked_unit' => 'integer',
        'appealed_status' => 'integer',
        'adjusted_amount' => 'integer',
        'actual_app_fee' => 'decimal:2',
        'monthly_reading' => 'boolean',

        'application_date' => 'datetime',
        'permission_date' => 'datetime',
        'valid_upto' => 'datetime',
        'rejection_date' => 'datetime',
        'amd_permission_date' => 'datetime',

        'deemed_aproval' => 'datetime',
        'auto_reject' => 'datetime',
    ];

    /**
     * Application Services Relation.
     */
    public function services(): HasMany
    {
        return $this->hasMany(
            GwApplicationService::class,
            'application_id',
            'application_id'
        );
    }

    /**
     * Application Logs Relation.
     */
    public function logs(): HasMany
    {
        return $this->hasMany(
            GwApplicationLog::class,
            'application_id',
            'application_id'
        );
    }

    public function isFresh()
    {
        return collect($this->service_codes)->contains(function ($code) {
            return in_array($code, ServiceType::gwFreshCodes());
        });
    }

    public function isIntimationApp(): bool
    {
        return collect($this->service_codes)->contains(function ($code) {
            return in_array($code, ServiceType::gwIntimationCodes());
        });
    }

    public function isAmendmentApp(): bool
    {
        return !$this->isFresh() && !$this->isIntimationApp();
    }

    public function isApproved(): bool
    {
        return $this->application_status === ApplicationStatus::Approved->value;
    }

    public function isSubmitted(): bool
    {
        return $this->application_status === ApplicationStatus::Submitted->value
            && $this->payment_status === 1;
    }

    public function isInDraft(){
        return $this->application_status === ApplicationStatus::Draft->value
            || (
                $this->application_status === ApplicationStatus::Submitted->value
                && $this->payment_status !== 1
                && !$this->payment()->exists()
            );
    }

    public function isInProcess(): bool
    {
        return $this->application_status === ApplicationStatus::Submitted->value
            && $this->payment_status === 1
            && $this->payment !== null;
    }

    public function isReturned(): bool
    {
        return $this->application_status === ApplicationStatus::Objection->value;
    }

    public function isRejected(): bool
    {
        return $this->application_status === ApplicationStatus::Rejected->value;
    }

    public function payment():HasOne
    {
        return $this->hasOne(
            Payment::class,
            'application_id',
            'application_id'
        )
        ->where([
            'application_type' => 'GW',
            'status' => 1,
        ])
        ->latest('id');
    }

}