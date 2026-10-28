<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

/**
 * Quản lý sản phẩm, upload ảnh qua ImageService (FR-10).
 */
class ProductController extends Controller
{
    public function index(Request $request)
    {
        abort(501); // TODO [T-21]
    }

    public function create()
    {
        abort(501); // TODO [T-19]
    }

    public function store(Request $request)
    {
        abort(501); // TODO [T-19, T-20]
    }

    public function edit(Product $product)
    {
        abort(501); // TODO [T-19]
    }

    public function update(Request $request, Product $product)
    {
        abort(501); // TODO [T-19, T-20]
    }

    public function destroy(Product $product)
    {
        abort(501); // TODO [T-21]
    }
}
