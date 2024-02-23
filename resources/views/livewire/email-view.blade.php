<div class="w-full h-full p-8 text-gray-700 bg-white rounded-lg shadow-lg dark:bg-gray-800 dark:text-gray-300">
    @if ($email)
        <h1 class="text-2xl">
            {{ $email->subject }}
        </h1>
        <div class="p-4 mt-4 bg-gray-200 rounded-lg dark:bg-gray-700">
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
    @else
        <div class="flex items-center justify-center w-full h-full gap-4 ">
            <div class="text-gray-700 dark:text-gray-300 align-center">
                <svg xmlns="http://www.w3.org/2000/svg"  viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"
                    class="w-[5rem] h-[5rem] mx-auto">
                    <path d="M17.7 7.7a2.5 2.5 0 1 1 1.8 4.3H2" />
                    <path d="M9.6 4.6A2 2 0 1 1 11 8H2" />
                    <path d="M12.6 19.4A2 2 0 1 0 14 16H2" />
                </svg>

                <h1 class="text-2xl font-bold">
                    {{ $this->emptyMessage }}
                </h1>
            </div>

        </div>
    @endif
</div>
