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
                    wire:click="showEmail({{ $email->id }})">
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
                                <div class="ml-auto">
                                    @if (!$email->read)
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                                        </svg>
                                    @else
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M21.75 9v.906a2.25 2.25 0 0 1-1.183 1.981l-6.478 3.488M2.25 9v.906a2.25 2.25 0 0 0 1.183 1.981l6.478 3.488m8.839 2.51-4.66-2.51m0 0-1.023-.55a2.25 2.25 0 0 0-2.134 0l-1.022.55m0 0-4.661 2.51m16.5 1.615a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V8.844a2.25 2.25 0 0 1 1.183-1.981l7.5-4.039a2.25 2.25 0 0 1 2.134 0l7.5 4.039a2.25 2.25 0 0 1 1.183 1.98V19.5Z" />
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
