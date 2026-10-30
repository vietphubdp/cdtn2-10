@extends('layouts.admin')

@php
    // Dùng chung cho trang thêm và trang sửa: SP chưa lưu ($product->exists = false) là form thêm
    $editing = $product->exists;
@endphp

@section('title', $editing ? 'Sửa sản phẩm' : 'Thêm sản phẩm')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h1 class="h3 mb-0">{{ $editing ? 'Sửa sản phẩm' : 'Thêm sản phẩm' }}</h1>
        <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">← Danh sách sản phẩm</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            {{-- enctype multipart để gửi được file ảnh --}}
            <form method="POST" enctype="multipart/form-data" novalidate
                  action="{{ $editing ? route('admin.products.update', $product) : route('admin.products.store') }}">
                @csrf
                @if ($editing)
                    @method('PUT')
                @endif

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="name" class="form-label">Tên <span class="text-danger">*</span></label>
                        <input type="text" id="name" name="name" value="{{ old('name', $product->name) }}"
                               class="form-control @error('name') is-invalid @enderror" maxlength="200" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        @if ($editing)
                            <div class="form-text">Slug hiện tại: {{ $product->slug }} (đổi tên thì slug đổi theo).</div>
                        @endif
                    </div>

                    <div class="col-md-6">
                        <label for="stock" class="form-label">Tồn kho <span class="text-danger">*</span></label>
                        <input type="number" id="stock" name="stock" value="{{ old('stock', $product->stock) }}"
                               class="form-control @error('stock') is-invalid @enderror" min="0" required>
                        @error('stock')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="category_id" class="form-label">Danh mục <span class="text-danger">*</span></label>
                        <select id="category_id" name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                            <option value="">-- Chọn danh mục --</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected(old('category_id', $product->category_id) == $category->id)>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="image" class="form-label">Ảnh (jpg/png ≤ 2MB)</label>
                        <input type="file" id="image" name="image" accept=".jpg,.jpeg,.png"
                               class="form-control @error('image') is-invalid @enderror">
                        @error('image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        @if ($product->image_url)
                            <div class="d-flex align-items-center gap-2 mt-2">
                                <img src="{{ $product->image_url }}" alt="{{ $product->name }}" width="64" height="64" class="rounded border object-fit-cover">
                                <span class="small text-secondary">Ảnh hiện tại – chọn tệp mới nếu muốn thay.</span>
                            </div>
                        @endif
                    </div>

                    <div class="col-6 col-md-3">
                        <label for="price" class="form-label">Giá <span class="text-danger">*</span></label>
                        <input type="number" id="price" name="price" value="{{ old('price', $product->price) }}"
                               class="form-control @error('price') is-invalid @enderror" min="0" required>
                        @error('price')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-6 col-md-3">
                        <label for="sale_price" class="form-label">Giá KM</label>
                        <input type="number" id="sale_price" name="sale_price" value="{{ old('sale_price', $product->sale_price) }}"
                               class="form-control @error('sale_price') is-invalid @enderror" min="0">
                        @error('sale_price')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Để trống nếu không giảm giá.</div>
                    </div>

                    <div class="col-md-6">
                        <label for="description" class="form-label">Mô tả</label>
                        <textarea id="description" name="description" rows="4"
                                  class="form-control @error('description') is-invalid @enderror">{{ old('description', $product->description) }}</textarea>
                        @error('description')
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
                               @checked(old('is_active', $product->is_active))>
                        <label for="is_active" class="form-check-label">Hiển thị</label>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
