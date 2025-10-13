<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'iso_code_2',
        'iso_code_3',
        'phone_code',
        'currency_code',
        'currency_symbol',
        'flag',
        'flag_emoji',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Relations
     */
    public function users()
    {
        return $this->hasMany(User::class, 'country_id');
    }
}