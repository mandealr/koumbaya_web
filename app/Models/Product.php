<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'images',
        'price',
        'ticket_price',
        'min_participants',
        'stock_quantity',
        'category_id',
        'merchant_id',
        'status',
        'is_featured',
    ];

    protected function casts(): array
    {
        return [
            'images' => 'array',
            'price' => 'decimal:2',
            'ticket_price' => 'decimal:2',
            'is_featured' => 'boolean',
        ];
    }

    /**
     * Relations
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function merchant()
    {
        return $this->belongsTo(User::class, 'merchant_id');
    }

    public function lotteries()
    {
        return $this->hasMany(Lottery::class, 'product_id');
    }

    public function activeLottery()
    {
        return $this->hasOne(Lottery::class, 'product_id')->where('status', 'active');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'product_id');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Accessors
     */
    public function getMainImageAttribute()
    {
        return $this->images ? $this->images[0] : null;
    }

    public function getIsAvailableAttribute()
    {
        return $this->status === 'active' && $this->stock_quantity > 0;
    }

    /**
     * Business Logic
     */
    public function canCreateLottery()
    {
        return $this->status === 'active' && 
               $this->stock_quantity > 0 && 
               !$this->activeLottery;
    }

    public function getTotalParticipationAmount()
    {
        return $this->ticket_price * $this->min_participants;
    }
}
