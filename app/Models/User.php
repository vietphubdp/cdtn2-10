<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\OrderStatus;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'phone', 'password', 'address'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_CUSTOMER = 'customer';

    public const ROLE_ADMIN = 'admin';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_locked' => 'boolean',
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /**
     * Id đơn "Hoàn thành" (mới nhất) có chứa SP này; null nếu khách chưa mua (BR-07).
     */
    public function completedOrderIdFor(Product $product): ?int
    {
        return $this->orders()
            ->where('status', OrderStatus::Completed)
            ->whereHas('items', fn ($query) => $query->where('product_id', $product->id))
            ->latest('id')
            ->value('id');
    }

    /**
     * Khách đã đánh giá SP này chưa – mỗi SP chỉ được đánh giá 1 lần (BR-07).
     */
    public function hasReviewed(Product $product): bool
    {
        return $this->reviews()->where('product_id', $product->id)->exists();
    }

    /**
     * Đủ điều kiện đánh giá: có đơn hoàn thành chứa SP và chưa đánh giá (BR-07).
     */
    public function canReview(Product $product): bool
    {
        return ! $this->hasReviewed($product) && $this->completedOrderIdFor($product) !== null;
    }
}
