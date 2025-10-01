<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use JsonSerializable;

class Customer extends Model implements JsonSerializable
{
    use HasFactory;

    protected $fillable = ['first_name', 'last_name', 'email', 'phone', 'birth_date', 'is_premium'];

    protected $dates = ['birth_date', 'created_at', 'updated_at'];

    protected $casts = [
        'is_premium' => 'boolean',
    ];

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function __toString()
    {
        return strtoupper($this->last_name) . ", $this->first_name [$this->email]";
    }

    public function jsonSerialize(): mixed
    {
        return [
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
        ];
    }
}
