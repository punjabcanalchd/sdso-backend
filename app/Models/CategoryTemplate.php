<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryTemplate extends Model
{
    protected $table = 'category_templates';
    protected $primaryKey = 'template_id';

    protected $fillable = [
        'name',
        'status',
        'display_on_home_page',
    ];
}
