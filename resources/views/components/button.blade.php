@props([
    'type' => 'button',
    'attributes' => [],
])
<button {{ $attributes->except('class') }} type="{{ $type }}" {{ $attributes->only('class')->merge(['class' => "bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800"]) }} >
    {{ $slot }}
</button>
