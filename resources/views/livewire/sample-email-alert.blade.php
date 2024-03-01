<div
    class="w-full p-8 bg-white rounded-lg shadow-lg dark:bg-gray-800 dark:text-gray-300 ">
    <span class="text-2xl font-bold">
        {!! $this->message !!}
    </span>
    <hr class="my-4 border-gray-700 dark:border-gray-300">
    <form wire:submit="sendSampleEmail">
        @csrf
        <x-mailweb::forms.input-label value="Send an example email to" />
        <x-mailweb::forms.input class="mt-1" type="email" name="email" placeholder="Email address"
            value="example@mailweb.com" wire:model="email" />
        <x-mailweb::forms.input-error :messages="$errors->get('email')" class="mt-2" />

        <div class="flex justify-end">
            <x-mailweb::button class="mt-4" type="submit">
                <div class="px-4 py-2 ">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-send"><path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/></svg>
                </div>
            </x-mailweb::button>
        </div>
    </form>
</div>
