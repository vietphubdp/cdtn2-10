<?php

namespace App\Http\Controllers;

class HomeController extends Controller
{
    /**
     * Trang chủ: SP mới, SP khuyến mãi (FR-03, T-08).
     */
    public function index()
    {
        // TODO [T-08]: thay bằng view shop.home
        return view('welcome');
    }
}
