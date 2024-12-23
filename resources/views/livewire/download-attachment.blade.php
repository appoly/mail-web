<div>
    <x-mailweb::button type="button" wire:click="download">
        {{ $attachment->name }}
    </x-mailweb::button>
    @if ($errorMessage)
        <div class="text-sm text-red-600">
            {{ $errorMessage }}
        </div>
    @endif

</div>
