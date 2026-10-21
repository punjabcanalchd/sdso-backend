<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\HasPublicId;

class ClientQuery extends Model
{
    use HasPublicId;

    protected $table = 'save_queries';
    protected $primaryKey = 'id';

    protected $fillable = [
        'name',
        'email',
        'contact',
        'subject',
        'details',
        'upload_pdf',
        'division_id',
        'water_entity_code',
        'subject_code',
        'status',
        'address',
    ];

    protected $casts = [
        'status' => 'integer',
        'division_id' => 'integer',
        'subject_code' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = [
        'public_id',
    ];

    public function division(): BelongsTo
    {
        return $this->belongsTo(Divisions::class, 'division_id', 'division_id');
    }

    public function remarks(): HasMany
    {
        return $this->hasMany(ClientQueryRemark::class, 'query_id', 'id')->orderBy('created_at', 'desc');
    }

    public function grievanceSubject(): BelongsTo
    {
        return $this->belongsTo(QuerySubject::class, 'subject_code', 'id');
    }
}
