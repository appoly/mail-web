<div class="w-full h-full p-8 text-gray-700 bg-white rounded-lg shadow-lg dark:bg-gray-800 dark:text-gray-300">
    @if ($email)
        <div class="p-4 bg-gray-200 rounded-lg dark:bg-gray-700">
            <h1 class="mb-4 text-2xl">
                {{ $email->subject }}
            </h1>
            <div class="flex gap-4">
                <div class="flex-shrink-0">
                    <img src="https://ui-avatars.com/api/?name={{ $email->from[0]['name'] }}"
                        class="w-[40px] rounded-full">
                </div>
                <div class="flex flex-col gap-1">
                    <h1 class="font-bold text-md dark:text-gray-200">
                        @foreach ($email->from as $from)
                            {{ $from['name'] }} <span
                                class="text-sm text-gray-700 dark:text-gray-300">({{ $from['address'] }})</span>
                        @endforeach
                    </h1>
                    <h1 class="font-bold text-md dark:text-gray-200">
                        @foreach ($email->to as $to)
                            {{ $to['name'] }} <span
                                class="text-sm text-gray-700 dark:text-gray-300">({{ $to['address'] }})</span>
                        @endforeach
                </div>
            </div>

            <p class="mt-4 text-sm text-gray-700 dark:text-gray-300">
                {!! $email->body_html !!}
            </p>
        </div>

        {{-- if route is mailweb.index only --}}
        @if (!$this->showOnly)
        <div class="flex flex-col ">
            <x-mailweb::button class="my-4" type="button" wire:click="toggleShare">
                @if ($email->share_enabled)
                    Disable Sharing
                @else
                    <div class="flex justify-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="1.25" stroke-linecap="round"
                            stroke-linejoin="round" class="lucide lucide-share">
                            <path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8" />
                            <polyline points="16 6 12 2 8 6" />
                            <line x1="12" x2="12" y1="2" y2="15" />
                        </svg>
                        <span>Enable Sharing</span>
                    </div>
                @endif
            </x-mailweb::button>

            @if ($email->share_enabled)
                <x-mailweb::forms.input-label value="Share Link" />
                <x-mailweb::forms.input id="shareLink" value="{{ route('mailweb.show', $email->id) }}" disabled/>
            @endif
        </div>
        @endif
    @else
        <div class="flex justify-center w-full h-full gap-4 align-middle ">
            <div class="my-auto text-gray-700 dark:text-gray-300">
                {!! $this->emptyIcon !!}
                <h1 class="text-2xl font-bold">
                    {{ $this->emptyMessage }}
                </h1>
            </div>

        </div>
    @endif
</div>