<a
    href="{{ route('cart.index') }}"
    class="relative inline-flex items-center justify-center rounded-md p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500"
    aria-label="Cart"
>
    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.5l1.5 15h13.5l2.25-9H6" />
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 21a.75.75 0 100-1.5A.75.75 0 009 21zm9 0a.75.75 0 100-1.5A.75.75 0 0018 21z" />
    </svg>

    @if ($count > 0)
        <span class="absolute -top-1 -right-1 inline-flex min-w-5 items-center justify-center rounded-full bg-gray-900 px-1.5 py-0.5 text-[10px] font-semibold leading-none text-white ring-2 ring-white">
            {{ $count > 99 ? '99+' : $count }}
        </span>
    @endif
</a>

