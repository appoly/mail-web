<div>
    <x-mailweb::button type="button" wire:click="download" wire:loading.attr="disabled" wire:loading.class="opacity-50">
        <span wire:loading.remove>{{ $attachment->name }}</span>
        <span wire:loading>Downloading...</span>
    </x-mailweb::button>
    @if ($errorMessage)
        <div class="text-sm text-red-600">
            {{ $errorMessage }}
        </div>
    @endif

</div>
