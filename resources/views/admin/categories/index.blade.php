@extends('layouts.admin')

@section('title', 'Danh mục')

@section('content')
    <h1 class="h3 mb-4">Danh mục</h1>

    @if (session('success'))
        <div class="alert alert-success" role="alert">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
    @endif

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Tên danh mục</th>
                                <th>Slug</th>
                                <th class="text-center">Số SP</th>
                                <th class="text-end">Sửa</th>
                                <th class="text-end">Xóa</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($categories as $category)
                                <tr @class(['table-active' => $editing?->is($category)])>
                                    <td class="fw-semibold">{{ $category->name }}</td>
                                    <td class="text-secondary">{{ $category->slug }}</td>
                                    <td class="text-center">{{ $category->products_count }}</td>
                                    <td class="text-end text-nowrap">
                                        <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-sm btn-outline-primary">Sửa</a>
                                    </td>
                                    <td class="text-end text-nowrap">
                                        {{-- FR-09: xóa phải qua hộp xác nhận --}}
                                        <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" class="d-inline"
                                              onsubmit="return confirm(@js('Xóa danh mục "'.$category->name.'"?'))">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Xóa</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-secondary py-4">Chưa có danh mục nào.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <p class="small text-secondary mt-2 mb-0">Danh mục còn sản phẩm (kể cả sản phẩm đang ẩn) không xóa được.</p>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm" id="category-form">
                <div class="card-body">
                    <h2 class="h6 text-uppercase fw-bold mb-3">{{ $editing ? 'Sửa danh mục' : 'Thêm danh mục' }}</h2>

                    <form method="POST" novalidate
                          action="{{ $editing ? route('admin.categories.update', $editing) : route('admin.categories.store') }}">
                        @csrf
                        @if ($editing)
                            @method('PUT')
                        @endif

                        <div class="mb-3">
                            <label for="name" class="form-label">Tên danh mục <span class="text-danger">*</span></label>
                            <input type="text" id="name" name="name" value="{{ old('name', $editing?->name) }}"
                                   class="form-control @error('name') is-invalid @enderror" maxlength="100" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Slug tự sinh từ tên.</div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Mô tả</label>
                            <textarea id="description" name="description" rows="3"
                                      class="form-control @error('description') is-invalid @enderror">{{ old('description', $editing?->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-dark">Lưu</button>
                        @if ($editing)
                            <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">Hủy</a>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
