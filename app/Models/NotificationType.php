<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NotificationType extends Model
{
    use HasFactory;

    protected $fillable = [
        'type_id',
        'name',
        'description',
        'enabled',
        'category'
    ];

    protected $casts = [
        'enabled' => 'boolean'
    ];

    /**
     * Get enabled notification types
     */
    public static function getEnabled()
    {
        return static::where('enabled', true)
            ->orderBy('category')
            ->orderBy('name')
            ->get();
    }

    /**
     * Get notification types by category
     */
    public static function getByCategory(string $category)
    {
        return static::where('category', $category)
            ->orderBy('name')
            ->get();
    }
}
