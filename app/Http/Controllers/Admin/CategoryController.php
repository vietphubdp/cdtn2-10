<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

/**
 * Quản lý danh mục (FR-09).
 */
class CategoryController extends Controller
{
    public function index()
    {
        abort(501); // TODO [T-17]
    }

    public function create()
    {
        abort(501); // TODO [T-17]
    }

    public function store(Request $request)
    {
        abort(501); // TODO [T-17]
    }

    public function edit(Category $category)
    {
        abort(501); // TODO [T-17]
    }

    public function update(Request $request, Category $category)
    {
        abort(501); // TODO [T-17]
    }

    public function destroy(Category $category)
    {
        abort(501); // TODO [T-18]
    }
}
