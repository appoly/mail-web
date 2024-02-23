@props([
    'type' => 'button',
    'attributes' => [],
])
<button {{ $attributes->except('class') }} type="{{ $type }}" {{ $attributes->only('class')->merge(['class' => "items-center w-full px-5 py-3 mb-3 mr-1 text-base font-semibold text-white no-underline align-middle bg-blue-600 border border-transparent border-solid rounded-md cursor-pointer select-none sm:mb-0 sm:w-auto hover:bg-blue-700 hover:border-blue-700 hover:text-white focus-within:bg-blue-700 focus-within:border-blue-700 transition"]) }}>
    {{ $slot }}
</button>
