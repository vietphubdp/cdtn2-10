{{--
    Icon SVG nét mảnh dùng chung cho trang khách (không cài thêm thư viện icon).
    Cách dùng: @include('partials.icon', ['name' => 'cart'])  ·  tùy chọn 'size' => 20
--}}
@php($size = $size ?? 20)
<svg xmlns="http://www.w3.org/2000/svg" width="{{ $size }}" height="{{ $size }}" viewBox="0 0 24 24" fill="none"
     stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="icon">
    @switch($name)
        @case('search')
            <circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>
            @break
        @case('cart')
            <path d="M3 4h2l2.4 10.2a1.5 1.5 0 0 0 1.5 1.1h8.4a1.5 1.5 0 0 0 1.5-1.1L21 8H6.2"/><circle cx="9.5" cy="19.5" r="1.3"/><circle cx="17" cy="19.5" r="1.3"/>
            @break
        @case('user')
            <circle cx="12" cy="12" r="9.5"/><circle cx="12" cy="10" r="3.2"/><path d="M6.2 18.4a7 7 0 0 1 11.6 0"/>
            @break
        @case('trash')
            <path d="M4 7h16M10 11v6M14 11v6M5.5 7l1 12a2 2 0 0 0 2 1.8h7a2 2 0 0 0 2-1.8l1-12M9 7V4.5h6V7"/>
            @break
        @case('arrow-right')
            <path d="M5 12h14M13 6l6 6-6 6"/>
            @break
        @case('chevron-right')
            <path d="m9 6 6 6-6 6"/>
            @break
        @case('filter')
            <path d="M4 6h10M18 6h2M4 12h4M12 12h8M4 18h12M20 18h0"/><circle cx="16" cy="6" r="2"/><circle cx="10" cy="12" r="2"/><circle cx="18" cy="18" r="2"/>
            @break
        @case('check-circle')
            <circle cx="12" cy="12" r="9.5" fill="currentColor" stroke="none"/><path d="m8 12.3 2.7 2.7L16 9.7" stroke="#fff" stroke-width="2"/>
            @break
        @case('truck')
            <path d="M3 6h11v10H3zM14 9h4l3 3.5V16h-7"/><circle cx="7" cy="17.5" r="1.8"/><circle cx="17" cy="17.5" r="1.8"/>
            @break
        @case('shield')
            <path d="M12 3 5 6v5.5c0 4.2 3 7.7 7 9.5 4-1.8 7-5.3 7-9.5V6z"/><path d="m9 12 2.2 2.2L15.5 10"/>
            @break
        @case('cash')
            <rect x="2.5" y="6" width="19" height="12" rx="2"/><circle cx="12" cy="12" r="2.8"/><path d="M6 9.5v5M18 9.5v5"/>
            @break
        @case('refresh')
            <path d="M20 11a8 8 0 0 0-14.6-4.5L4 8M4 4v4h4M4 13a8 8 0 0 0 14.6 4.5L20 16M20 20v-4h-4"/>
            @break
        @case('sparkle')
            <path d="M12 1.5c.7 5.6 4.9 9.8 10.5 10.5-5.6.7-9.8 4.9-10.5 10.5C11.3 16.9 7.1 12.7 1.5 12 7.1 11.3 11.3 7.1 12 1.5z" fill="currentColor" stroke="none"/>
            @break
    @endswitch
</svg>
