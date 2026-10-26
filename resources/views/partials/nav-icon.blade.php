<svg class="bs-nav-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    @switch($icon)
        @case('home')
            <path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-8H9v8H4a1 1 0 0 1-1-1Z" />
            @break
        @case('box')
            <path d="m12 3 9 5v9l-9 5-9-5V8Zm0 9v10M3 8l9 4 9-4M7.5 5.5l9 5" />
            @break
        @case('grid')
            <rect x="3" y="3" width="7" height="7" rx="2" /><rect x="14" y="3" width="7" height="7" rx="2" /><rect x="3" y="14" width="7" height="7" rx="2" /><rect x="14" y="14" width="7" height="7" rx="2" />
            @break
        @case('user')
            <circle cx="12" cy="8" r="4" /><path d="M4 21v-2a8 8 0 0 1 16 0v2" />
            @break
        @case('orders')
            <rect x="5" y="4" width="14" height="17" rx="2" /><path d="M9 4V2h6v2M9 10h6M9 15h6" />
            @break
        @case('admin')
            <path d="m12 3 8 3v6c0 5-8 9-8 9s-8-4-8-9V6Z" /><path d="m8 12 3 3 5-5" />
            @break
        @case('logout')
            <path d="M9 4H4v16h5M9 12h12m-4-4 4 4-4 4" />
            @break
        @case('chevron')
            <path d="m6 9 6 6 6-6" />
            @break
    @endswitch
</svg>
