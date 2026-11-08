@extends('layouts.admin')

@php
    // Dùng chung cho trang thêm và trang sửa
    $editing = $coupon->exists;
@endphp

@section('title', $editing ? 'Sửa mã giảm giá' : 'Thêm mã giảm giá')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h1 class="h3 mb-0">{{ $editing ? 'Sửa mã giảm giá' : 'Thêm mã giảm giá' }}</h1>
        <a href="{{ route('admin.coupons.index') }}" class="btn btn-outline-secondary">← Danh sách mã</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" novalidate
                  action="{{ $editing ? route('admin.coupons.update', $coupon) : route('admin.coupons.store') }}">
                @csrf
                @if ($editing)
                    @method('PUT')
                @endif

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="code" class="form-label">Mã <span class="text-danger">*</span></label>
                        <input type="text" id="code" name="code" value="{{ old('code', $coupon->code) }}" maxlength="30"
                               class="form-control text-uppercase @error('code') is-invalid @enderror" required>
                        @error('code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Chữ, số, dấu - hoặc _; tự chuyển thành chữ in hoa.</div>
                    </div>

                    <div class="col-md-6">
                        <label for="type" class="form-label">Loại giảm <span class="text-danger">*</span></label>
                        <select id="type" name="type" class="form-select @error('type') is-invalid @enderror" required>
                            <option value="percent" @selected(old('type', $coupon->type) === 'percent')>Theo phần trăm (%)</option>
                            <option value="fixed" @selected(old('type', $coupon->type) === 'fixed')>Số tiền cố định (đ)</option>
                        </select>
                        @error('type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-6 col-md-3">
                        <label for="value" class="form-label">Giá trị <span class="text-danger">*</span></label>
                        <input type="number" id="value" name="value" value="{{ old('value', $coupon->value) }}" min="1"
                               class="form-control @error('value') is-invalid @enderror" required>
                        @error('value')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">% (1–100) hoặc số tiền.</div>
                    </div>

                    <div class="col-6 col-md-3">
                        <label for="max_discount" class="form-label">Giảm tối đa (đ)</label>
                        <input type="number" id="max_discount" name="max_discount" value="{{ old('max_discount', $coupon->max_discount) }}" min="1"
                               class="form-control @error('max_discount') is-invalid @enderror">
                        @error('max_discount')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Chỉ dùng cho mã %; trống = không giới hạn.</div>
                    </div>

                    <div class="col-6 col-md-3">
                        <label for="min_order" class="form-label">Đơn tối thiểu (đ)</label>
                        <input type="number" id="min_order" name="min_order" value="{{ old('min_order', $coupon->min_order) }}" min="0"
                               class="form-control @error('min_order') is-invalid @enderror">
                        @error('min_order')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-6 col-md-3">
                        <label for="usage_limit" class="form-label">Số lượt</label>
                        <input type="number" id="usage_limit" name="usage_limit" value="{{ old('usage_limit', $coupon->usage_limit) }}" min="1"
                               class="form-control @error('usage_limit') is-invalid @enderror">
                        @error('usage_limit')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">
                            Trống = không giới hạn.
                            @if ($editing)
                                Đã dùng {{ $coupon->used_count }} lượt.
                            @endif
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label for="starts_at" class="form-label">Bắt đầu <span class="text-danger">*</span></label>
                        <input type="datetime-local" id="starts_at" name="starts_at"
                               value="{{ old('starts_at', $coupon->starts_at?->format('Y-m-d\TH:i')) }}"
                               class="form-control @error('starts_at') is-invalid @enderror" required>
                        @error('starts_at')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="expires_at" class="form-label">Hết hạn <span class="text-danger">*</span></label>
                        <input type="datetime-local" id="expires_at" name="expires_at"
                               value="{{ old('expires_at', $coupon->expires_at?->format('Y-m-d\TH:i')) }}"
                               class="form-control @error('expires_at') is-invalid @enderror" required>
                        @error('expires_at')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="d-flex align-items-center gap-3 mt-4">
                    <button type="submit" class="btn btn-dark">Lưu</button>
                    <div class="form-check mb-0">
                        {{-- Ô bỏ tick thì trình duyệt không gửi gì → input ẩn gửi 0 --}}
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" id="is_active" name="is_active" value="1" class="form-check-input"
                               @checked(old('is_active', $coupon->is_active))>
                        <label for="is_active" class="form-check-label">Đang bật</label>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
