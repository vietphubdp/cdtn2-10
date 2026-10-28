<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

#[Fillable(['category_id', 'name', 'slug', 'price', 'sale_price', 'stock', 'image', 'description', 'is_active'])]
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'sale_price' => 'integer',
            'stock' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Chỉ lấy SP đang hiển thị cho khách (BR-02).
     */
    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * Giá bán = giá khuyến mãi nếu có, ngược lại giá gốc (BR-01).
     */
    protected function finalPrice(): Attribute
    {
        return Attribute::get(fn () => $this->sale_price ?? $this->price);
    }

    protected function isOnSale(): Attribute
    {
        return Attribute::get(fn () => $this->sale_price !== null);
    }

    protected function inStock(): Attribute
    {
        return Attribute::get(fn () => $this->stock > 0);
    }

    /**
     * URL ảnh để hiển thị; null nếu SP chưa có ảnh.
     */
    protected function imageUrl(): Attribute
    {
        return Attribute::get(fn () => $this->image ? Storage::url($this->image) : null);
    }
}
