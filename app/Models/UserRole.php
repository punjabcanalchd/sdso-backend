<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasPublicId;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class UserRole extends Model
{
    use HasFactory, HasPublicId, LogsActivity;

    protected $primaryKey = 'role_id';

    protected $fillable = [
        'name',
        'permissions',
        'role_id',
    ];

    protected $appends = [
        'public_id'
    ];

    protected $hidden = [
        'role_id'
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }
}