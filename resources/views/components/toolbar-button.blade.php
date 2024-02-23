@props([
    'type' => 'button',
    'attributes' => [],
])
<button {{ $attributes->except('class') }} type="{{ $type }}" {{ $attributes->only('class')->merge(['class' => "text-gray-900 hover:bg-blue-700 hover:text-white font-bold py-2 px-4 focus:outline-none focus:shadow-outline dark:hover:bg-blue-700 dark:focus:ring-blue-800 transition-[background-color] ease-in-out duration-300 dark:text-white"]) }}>
    {{ $slot }}
</button>