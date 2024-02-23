<div
    class="w-full p-8 bg-orange-500 border rounded-lg shadow-lg dark:bg-gray-800 dark:text-gray-300 dark:border-orange-500">
    <span class="text-2xl font-bold">
        {!! $this->message !!}
    </span>
    <hr class="my-4 border-gray-700 dark:border-gray-300">
    <form wire:submit="sendSampleEmail">
        @csrf
        <x-mailweb::forms.input-label value="Send an example email to" />
        <x-mailweb::forms.input class="mt-1" type="email" name="email" placeholder="Email address"
            value="example@mailweb.com" wire:model="email" />
        <x-input-error :messages="$errors->get('email')" class="mt-2" />

        <div class="flex justify-end">
            <x-mailweb::button class="mt-4" type="submit">
                Send
            </x-mailweb::button>
        </div>
    </form>
</div>
