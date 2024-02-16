<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mail Web</title>
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('/vendor/mailweb/icons/favicon.ico') }}">
    
    {{ Vite::useHotFile(public_path('vendor/mailweb'))->useBuildDirectory('/vendor/mailweb')->withEntryPoints(['resources/css/mailweb.css', 'resources/js/mailweb.js']) }}
    @livewireStyles
</head>
<body class="h-100 dark:bg-gray-900">
    <div id="app">
         @yield('content')
    </div>

    @livewireScripts
    <script src="{{ asset('/vendor/mailweb/app.js') }}" defer></script>
</body>
</html>
