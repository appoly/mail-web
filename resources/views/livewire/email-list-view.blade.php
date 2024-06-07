<div class="flex gap-1">
    <div @class([
        'max-h-screen overflow-y-hidden max-w-[35rem] transition-all duration-300',
        'w-0' => $hidden,
        'w-[35rem]' => !$hidden,
    ])>
        <x-mailweb::forms.input type="text" wire:model.live.debounce.250ms="search" placeholder="Search..."
            class="mb-4" />

        <div class="overflow-y-auto scrollbar-hide max-h-[calc(100vh-200px)]" wire:poll.10s.visible>
            <div class="flex flex-col gap-4">
                {{ $emails->links() }}

                @foreach ($emails as $email)
                    <div @class([
                        'transition-all duration-300 bg-white rounded-md shadow cursor-pointer dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 w-[95%] mx-auto',
                        'opacity-75' => $email->read && $activeEmailId != $email->id,
                        'opacity-100 shadow-2xl shadow-appoly-red/20' =>
                            $activeEmailId == $email->id,
                    ]) wire:click="showEmail('{{ $email->id }}')">

                        <div class="flex justify-end pt-2 pr-2">
                            @if (!$email->read)
                                <span class="relative flex w-3 h-3">
                                    <span
                                        class="absolute inline-flex w-full h-full rounded-full opacity-75 animate-ping bg-sky-400"></span>
                                    <span class="relative inline-flex w-3 h-3 rounded-full bg-appoly-red"></span>
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
                                        <p class="ml-auto text-xs text-gray-700 dark:text-gray-300" title="{{ $email->created_at }}>
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
    <div class="flex-shrink-0">
        <button wire:click="toggle"
            class="w-8 h-8 transition-all rounded-full hover:bg-appoly-red/50 dark:hover:bg-appoly-red/50">
            <div @class([
                'flex items-center justify-center gap-2 transition-all',
                'rotate-180' => $hidden,
            ])>
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="lucide lucide-chevron-right">
                    <path d="m9 18 6-6-6-6" />
                </svg>
            </div>
        </button>
    </div>
</div>
