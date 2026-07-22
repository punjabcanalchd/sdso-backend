<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasPublicId;

class UserRole extends Model
{
    use HasFactory;
    use HasPublicId;

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
}