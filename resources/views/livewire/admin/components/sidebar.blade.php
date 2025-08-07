<nav class="flex-1 px-6 py-6 space-y-1 overflow-y-auto">
    @foreach($navigationItems as $item)
        <a href="{{ Route::has($item['route']) ? route($item['route']) : '#' }}" 
           class="nav-item rounded-lg {{ Route::has($item['route']) && (request()->routeIs($item['route']) || request()->routeIs($item['route'].'.*')) ? 'active' : '' }}">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}"></path>
            </svg>
            {{ $item['name'] }}
        </a>
    @endforeach
</nav>