@props([
    'type' => 'button',
    'attributes' => [],
])

<button {{ $attributes->except('class') }} type="{{ $type }}" {{ $attributes->only('class')->merge(['class' => "inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 bg-appoly-red text-white shadow hover:bg-appoly-red/90 h-9 px-4 py-3"]) }}>
    {{ $slot }}
</button>