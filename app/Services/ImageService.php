<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Lưu ảnh sản phẩm vào disk public (storage/app/public/products).
 * Trong DB chỉ lưu đường dẫn tương đối, hiển thị bằng Storage::url() hoặc $product->image_url.
 */
class ImageService
{
    private const DISK = 'public';

    private const FOLDER = 'products';

    /**
     * Lưu ảnh mới, trả về đường dẫn để ghi vào cột image.
     */
    public function store(UploadedFile $file): string
    {
        return $file->store(self::FOLDER, self::DISK);
    }

    /**
     * Đổi ảnh: lưu ảnh mới rồi xóa ảnh cũ (nếu có).
     */
    public function replace(?string $oldPath, UploadedFile $file): string
    {
        $newPath = $this->store($file);
        $this->delete($oldPath);

        return $newPath;
    }

    public function delete(?string $path): void
    {
        if ($path && Storage::disk(self::DISK)->exists($path)) {
            Storage::disk(self::DISK)->delete($path);
        }
    }
}
