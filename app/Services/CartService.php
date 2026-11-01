<?php

namespace App\Services;

use App\Exceptions\BusinessException;
use App\Models\Product;
use Illuminate\Support\Collection;

/**
 * Giỏ hàng lưu trong session 'cart' dạng [product_id => số lượng] (không có bảng carts).
 * Giá luôn lấy từ DB lúc đọc giỏ, không lưu trong session.
 */
class CartService
{
    private const KEY = 'cart';

    /**
     * [product_id => số lượng] đang lưu trong session.
     *
     * @return array<int, int>
     */
    public function quantities(): array
    {
        return session(self::KEY, []);
    }

    /**
     * Các dòng trong giỏ, bỏ qua SP đã bị ẩn/xóa.
     *
     * @return Collection<int, array{product: Product, quantity: int, line_total: int}>
     */
    public function items(): Collection
    {
        $quantities = $this->quantities();
        $products = Product::active()->whereIn('id', array_keys($quantities))->get()->keyBy('id');

        return collect($quantities)
            ->filter(fn ($quantity, $productId) => $products->has($productId))
            ->map(fn ($quantity, $productId) => [
                'product' => $products[$productId],
                'quantity' => $quantity,
                'line_total' => $products[$productId]->final_price * $quantity,
            ])
            ->values();
    }

    /**
     * Tạm tính theo giá bán hiện tại (BR-01).
     */
    public function subtotal(): int
    {
        return $this->items()->sum('line_total');
    }

    /**
     * Tổng số lượng, dùng cho số trên icon giỏ ở header.
     */
    public function count(): int
    {
        return array_sum($this->quantities());
    }

    public function isEmpty(): bool
    {
        return $this->quantities() === [];
    }

    /**
     * Thêm SP vào giỏ; cộng dồn nếu đã có. Tổng số lượng không vượt tồn kho (BR-03).
     */
    public function add(Product $product, int $quantity = 1): void
    {
        if (! $product->is_active) {
            throw new BusinessException('Sản phẩm không còn được bán.');
        }

        $quantities = $this->quantities();
        $newQuantity = ($quantities[$product->id] ?? 0) + $quantity;
        $this->ensureInStock($product, $newQuantity);

        $quantities[$product->id] = $newQuantity;
        session([self::KEY => $quantities]);
    }

    /**
     * Đổi số lượng 1 dòng; số lượng ≤ 0 thì xóa dòng đó.
     */
    public function update(int $productId, int $quantity): void
    {
        if ($quantity <= 0) {
            $this->remove($productId);

            return;
        }

        $product = Product::active()->find($productId);
        if (! $product) {
            $this->remove($productId);
            throw new BusinessException('Sản phẩm không còn được bán, đã xóa khỏi giỏ hàng.');
        }

        $this->ensureInStock($product, $quantity);

        $quantities = $this->quantities();
        $quantities[$productId] = $quantity;
        session([self::KEY => $quantities]);
    }

    public function remove(int $productId): void
    {
        $quantities = $this->quantities();
        unset($quantities[$productId]);
        session([self::KEY => $quantities]);
    }

    public function clear(): void
    {
        session()->forget(self::KEY);
    }

    /**
     * Kiểm tra lại giỏ với tồn kho hiện tại (BR-03): giảm số lượng vượt tồn kho,
     * xóa SP đã hết hàng/bị ẩn. Trả về các thông báo để controller flash cho khách.
     *
     * @return array<int, string>
     */
    public function syncWithStock(): array
    {
        $quantities = $this->quantities();
        $products = Product::active()->whereIn('id', array_keys($quantities))->get()->keyBy('id');
        $messages = [];

        foreach ($quantities as $productId => $quantity) {
            $product = $products->get($productId);

            if (! $product || $product->stock === 0) {
                unset($quantities[$productId]);
                $messages[] = $product
                    ? 'Sản phẩm "'.$product->name.'" đã hết hàng, đã xóa khỏi giỏ.'
                    : 'Một sản phẩm không còn được bán, đã xóa khỏi giỏ.';
            } elseif ($quantity > $product->stock) {
                $quantities[$productId] = $product->stock;
                $messages[] = 'Sản phẩm "'.$product->name.'" chỉ còn '.$product->stock.', đã giảm số lượng trong giỏ.';
            }
        }

        session([self::KEY => $quantities]);

        return $messages;
    }

    private function ensureInStock(Product $product, int $quantity): void
    {
        if ($product->stock === 0) {
            throw new BusinessException('Sản phẩm "'.$product->name.'" đã hết hàng.');
        }

        if ($quantity > $product->stock) {
            throw new BusinessException('Sản phẩm "'.$product->name.'" chỉ còn '.$product->stock.' trong kho.');
        }
    }
}
