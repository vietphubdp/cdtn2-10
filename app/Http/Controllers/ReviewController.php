<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * Đánh giá SP đã mua (FR-12, BR-07).
 */
class ReviewController extends Controller
{
    public function store(Request $request, string $slug)
    {
        abort(501); // TODO Sprint 3 (FR-12)
    }
}
