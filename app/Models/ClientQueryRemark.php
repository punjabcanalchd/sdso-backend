<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientQueryRemark extends Model
{
    protected $table = 'remarks_for_queries';
    protected $primaryKey = 'id';

    protected $fillable = [
        'query_id',
        'remark',
        'status',
    ];

    protected $casts = [
        'status' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function clientQuery(): BelongsTo
    {
        return $this->belongsTo(ClientQuery::class, 'query_id', 'id');
    }
}
