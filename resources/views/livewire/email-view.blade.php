<div class="w-full h-full p-8 text-gray-700 bg-white rounded-lg shadow-lg dark:bg-gray-800 dark:text-gray-300">

    {{-- toolbar with, html, source, text --}}
    @if ($email)
        @if (!$this->showOnly)
            <nav class="flex justify-between mb-4 bg-gray-200 dark:bg-gray-700">
                <div class="flex gap-4">
                    <x-mailweb::toolbar-button wire:click="toggleEmailView" @class(['border-b-2 border-b-blue-500' => $this->mode === 'email'])>
                        <div class="flex gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="1.25" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-mail">
                                <rect width="20" height="16" x="2" y="4" rx="2" />
                                <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7" />
                            </svg> <span>Email</span>
                        </div>
                    </x-mailweb::toolbar-button>
                    <x-mailweb::toolbar-button wire:click="toggleSource" @class(['border-b-2 border-b-blue-500' => $this->mode === 'source'])>
                        <div class="flex gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="1.25" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-code-2">
                                <path d="m18 16 4-4-4-4" />
                                <path d="m6 8-4 4 4 4" />
                                <path d="m14.5 4-5 16" />
                            </svg>
                            <span>Source</span>
                        </div>
                    </x-mailweb::toolbar-button>
                    <x-mailweb::toolbar-button wire:click="toggleText" @class(['border-b-2 border-b-blue-500' => $this->mode === 'text'])>
                        <div class="flex gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="1.25" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-text">
                                <path d="M17 6.1H3" />
                                <path d="M21 12.1H3" />
                                <path d="M15.1 18H3" />
                            </svg>
                            <span>Text</span>
                        </div>
                    </x-mailweb::toolbar-button>
                    <x-mailweb::toolbar-button wire:click="setSize('desktop')">
                        <div class="flex gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="1.25" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-laptop">
                                <path
                                    d="M20 16V7a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v9m16 0H4m16 0 1.28 2.55a1 1 0 0 1-.9 1.45H3.62a1 1 0 0 1-.9-1.45L4 16" />
                            </svg>
                            <span>Desktop</span>
                        </div>
                    </x-mailweb::toolbar-button>
                    <x-mailweb::toolbar-button wire:click="setSize('mobile')">
                        <div class="flex gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="1.25" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-smartphone">
                                <rect width="14" height="20" x="5" y="2" rx="2" ry="2" />
                                <path d="M12 18h.01" />
                            </svg>
                            <span>Mobile</span>
                        </div>
                    </x-mailweb::toolbar-button>
                </div>
                <div class="flex gap-4">
                    <x-mailweb::toolbar-button wire:click="delete" class="hover:bg-red-500 dark:hover:bg-red-600">
                        <div class="flex gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="1.25" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-trash-2">
                                <path d="M3 6h18" />
                                <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6" />
                                <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2" />
                                <line x1="10" x2="10" y1="11" y2="17" />
                                <line x1="14" x2="14" y1="11" y2="17" />
                            </svg>
                            <span>Delete</span>
                        </div>
                    </x-mailweb::toolbar-button>
                </div>

            </nav>
        @endif

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
            @if ($email->attachments)
                <div class="mt-4">
                    <span class="mb-2 text-sm font-bold text-gray-700 dark:text-gray-300">Attachments:</span>
                    <div class="flex gap-4 ">
                        @foreach ($email->attachments as $attachment)
                            <div
                                class="flex items-center justify-center w-24 h-10 bg-gray-200 rounded-lg dark:bg-gray-800">
                                <span class="text-sm text-gray-500 dark:text-gray-300">
                                    {{ $attachment->name }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="w-full">
                <div @class([
                    'mt-4 transition-all flex justify-center',
                    'lg:w-1/4' => $this->size === 'mobile',
                ])>
                <div class="w-full">
                    @switch($this->mode)
                        @case('email')
                            {!! $email->body_html !!}
                        @break

                        @case('source')
                            <pre class="whitespace-pre-wrap">{{ $email->body_html }}</pre>
                        @break

                        @case('text')
                            <pre class="whitespace-pre-wrap">{{ $email->body_text }}</pre>
                        @break

                        @default
                    @endswitch
                </div>
                </div>
            </div>
        </div>

        @if (!$this->showOnly)
            <div class="flex flex-col ">
                <x-mailweb::button class="my-4" type="button" wire:click="toggleShare">
                    @if ($email->share_enabled)
                        Disable Sharing
                    @else
                        <div class="flex justify-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.25"
                                stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-share">
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
                    <x-mailweb::forms.input id="shareLink" value="{{ route('mailweb.show', $email->id) }}" disabled />
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
