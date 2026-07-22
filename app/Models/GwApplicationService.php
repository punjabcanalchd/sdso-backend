<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\GwApplication;

class GwApplicationService extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'gw_application_services';

    /**
     * The primary key associated with the table.
     */
    protected $primaryKey = 'id';

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
    protected $fillable = [
        'application_id',
        'service_type',
    ];

    /**
     * Attribute casting.
     */
    protected $casts = [
        'application_id' => 'integer',
        'service_type'   => 'integer',
    ];

    /**
     * Application relation.
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(
            GwApplication::class,
            'application_id',
            'application_id'
        );
    }
}