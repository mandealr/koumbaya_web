<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'method_id',
        'name',
        'description',
        'type',
        'icon',
        'active',
        'config',
        'gateway',
        'sort_order'
    ];

    protected $casts = [
        'active' => 'boolean',
        'config' => 'array',
        'sort_order' => 'integer'
    ];

    /**
     * Get active payment methods
     */
    public static function getActive()
    {
        return static::where('active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    /**
     * Get payment methods by type
     */
    public static function getByType(string $type)
    {
        return static::where('type', $type)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }
}
