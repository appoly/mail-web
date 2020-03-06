<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />

    <link href="{{ asset("/vendor/mailweb/app.css") }}" rel="stylesheet" />
</head>

<body class='max-w-full'>
    <div id="app">
        @yield('content')
    </div>
</body>

<script src="{{ asset("/vendor/mailweb/app.js") }}" defer></script>

</html>