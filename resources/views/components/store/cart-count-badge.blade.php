@props(['count' => 0])

<span
    data-bf-cart-count
    @class([
        'absolute -top-1 -right-1 min-w-[1.25rem] h-5 px-1 bg-red-600 text-white text-xs font-semibold rounded-full flex items-center justify-center leading-none pointer-events-none',
        'hidden' => (int) $count < 1,
    ])
>{{ (int) $count }}</span>
