<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'slug',
        'color',
        'is_active'
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    protected $casts = ['is_active' => 'boolean'];

}
