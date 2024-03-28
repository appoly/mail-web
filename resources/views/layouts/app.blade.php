<!DOCTYPE html>
<html lang="en">
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ darkMode: false }"
    x-bind:class="{ 'dark': darkMode === true }" x-init="if (!('darkMode' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches) {
        localStorage.setItem('darkMode', JSON.stringify(true));
    }
    darkMode = JSON.parse(localStorage.getItem('darkMode'));
    $watch('darkMode', value => localStorage.setItem('darkMode', JSON.stringify(value)))">


<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Mail Web</title>
<link rel="shortcut icon" type="image/x-icon" href="{{ asset('/vendor/mailweb/icons/favicon.ico') }}">

{{ Vite::useHotFile(public_path('vendor/mailweb'))->useBuildDirectory('/vendor/mailweb')->withEntryPoints(['resources/css/mailweb.css', 'resources/js/mailweb.js']) }}
@livewireStyles
</head>

<body class="h-100 dark:bg-gray-900">
    <div id="app" class="min-h-screen py-8 text-gray-700 bg-slate-50 dark:bg-slate-900 sm:px-8 dark:text-white">
        <div class="flex justify-between">
            <h1 class="font-bold text-8xl">
                Mail Web
            </h1>
            <div class="justify-end">
                <div class="flex gap-4">
                    <a href="{{ config('app.url') }}"
                        class="text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none rounded-lg text-sm p-2.5">
                        <div class="flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-arrow-left">
                                <path d="m12 19-7-7 7-7" />
                                <path d="M19 12H5" />
                            </svg>
                            Return to {{ config('app.name') }}
                        </div>
                    </a>
                    <button x-on:click="darkMode = !darkMode" type="button"
                        class="text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none rounded-lg text-sm p-2.5">
                        {{-- <x-lucide-sun x-show="!darkMode" /> --}}
                        <svg x-show="!darkMode" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-sun">
                            <circle cx="12" cy="12" r="4" />
                            <path d="M12 2v2" />
                            <path d="M12 20v2" />
                            <path d="m4.93 4.93 1.41 1.41" />
                            <path d="m17.66 17.66 1.41 1.41" />
                            <path d="M2 12h2" />
                            <path d="M20 12h2" />
                            <path d="m6.34 17.66-1.41 1.41" />
                            <path d="m19.07 4.93-1.41 1.41" />
                        </svg>
                        <svg x-show="darkMode" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-moon">
                            <path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        @yield('content')
    </div>

    @livewireScripts
    <script src="{{ asset('/vendor/mailweb/app.js') }}" defer></script>
</body>

</html>
