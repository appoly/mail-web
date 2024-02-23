<div class="w-full max-h-screen overflow-y-hidden lg:w-1/3">
    {{-- search bar --}}

    <x-mailweb::forms.input type="text" wire:model.live.debounce.250ms="search" placeholder="Search..." class="mb-4" />

    <span class="my-4 text-gray-700 dark:text-gray-300">
        Showing {{ $emails->firstItem() ?? 0 }} to {{ $emails->lastItem() ?? 0 }} of {{ $emails->total() }} emails
    </span>
    <div class="overflow-y-auto scrollbar-hide max-h-[calc(100vh-200px)]">
        <div class="flex flex-col gap-4">
            @foreach ($emails as $email)
                <div class="px-4 pt-6 pb-8 bg-white rounded-lg shadow cursor-pointer dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700"
                    wire:click="showEmail('{{ $email->id }}')">
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
                                {{-- unread mark --}}
                                <div class="ml-auto text-gray-500 dark:text-gray-300">
                                    @if (!$email->read)
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.25"
                                            stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-mail">
                                            <rect width="20" height="16" x="2" y="4" rx="2" />
                                            <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7" />
                                        </svg>
                                    @else
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.25"
                                            stroke-linecap="round" stroke-linejoin="round"
                                            class="lucide lucide-mail-open">
                                            <path
                                                d="M21.2 8.4c.5.38.8.97.8 1.6v10a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V10a2 2 0 0 1 .8-1.6l8-6a2 2 0 0 1 2.4 0l8 6Z" />
                                            <path d="m22 10-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 10" />
                                        </svg>
                                    @endif
                                </div>
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
            @endforeach
        </div>
    </div>
</div>
