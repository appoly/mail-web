<div class="w-full max-h-screen overflow-y-hidden lg:w-1/3">
    {{-- search bar --}}

    <x-mailweb::forms.input type="text" wire:model.live.debounce.250ms="search" placeholder="Search..." class="mb-4" />

    <span class="my-4 text-gray-700 dark:text-gray-300">
        Showing {{ $emails->firstItem() ?? 0 }} to {{ $emails->lastItem() ?? 0 }} of {{ $emails->total() }} emails
    </span>
    <div class="overflow-y-auto scrollbar-hide max-h-[calc(100vh-200px)]">
        <div class="flex flex-col gap-4">
            @foreach ($emails as $email)
                <div class="bg-white rounded-lg shadow cursor-pointer dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700"
                    wire:click="showEmail('{{ $email->id }}')">
                    <div class="flex justify-end pt-2 pr-2">
                        @if (!$email->read)
                            <span class="relative flex w-3 h-3">
                                <span
                                    class="absolute inline-flex w-full h-full rounded-full opacity-75 animate-ping bg-sky-400"></span>
                                <span class="relative inline-flex w-3 h-3 rounded-full bg-sky-500"></span>
                            </span>
                        @endif
                    </div>
                    <div class="px-4 pt-2 pb-8 content">

                        <div class="flex gap-4">
                            <div class="flex-shrink-0">
                                <img src="https://ui-avatars.com/api/?name={{ $email->from[0]['name'] }}"
                                    class="rounded-full w-[40px]">
                            </div>
                            <div class="flex flex-col gap-1">
                                <div class="flex">
                                    <h1 class="font-bold text-md dark:text-gray-200">
                                        @foreach ($email->from as $from)
                                            {{ $from['name'] }} <span
                                                class="text-sm text-gray-700 dark:text-gray-300">({{ $from['address'] }})</span>
                                        @endforeach
                                    </h1>
                                </div>
                                <p class="text-sm text-gray-700 dark:text-gray-300">
                                    {{ $email->subject }}
                                </p>
                                <p class="text-xs text-gray-700 dark:text-gray-300">
                                    {{ $email->snippet }}
                                </p>
                                <div class="flex content-end gap-4">
                                    <p class="ml-auto text-xs text-gray-700 dark:text-gray-300">
                                        {{ $email->created_at->diffForHumans() }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
