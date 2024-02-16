<div>
    <div class="flex items-center">
        <h1 class="text-lg font-bold">Emails</h1>
    </div>
    <div class="max-w-lg mx-auto">
        @foreach ($emails as $email)
            <div class="px-4 pt-6 pb-8 mb-2 bg-white rounded-lg shadow-lg dark:bg-gray-800">
                <div class="flex gap-4">
                    <div class="flex-shrink-0">
                        <img src="https://ui-avatars.com/api/?name={{ $email->from[0]['name'] }}"
                            class="rounded-full">
                    </div>
                    <div class="flex flex-col gap-1">
                        <h1 class="font-bold text-md dark:text-gray-200">
                            @foreach ($email->from as $from)
                                {{ $from['name'] }} <span
                                    class="text-sm text-gray-700 dark:text-gray-300">({{ $from['address'] }})</span>
                            @endforeach
                        </h1>
                        <div class="flex">
                            <p class="text-sm text-gray-700 dark:text-gray-300">
                                {{ $email->subject }}
                            </p>
                            <p class="ml-auto text-xs text-gray-700 dark:text-gray-300">
                                {{ $email->created_at->diffForHumans() }}
                            </p>
                        </div>
                        <p class="text-xs text-gray-700 dark:text-gray-300">
                            {{ $email->snippet }}
                        </p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
