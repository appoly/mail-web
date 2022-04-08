<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />
    <title>Mail Web</title>
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset("/vendor/mailweb/icons/favicon.ico") }}" />
    <link href="{{ asset("/vendor/mailweb/app.css") }}" rel="stylesheet" />
</head>

<body>
    <div id="app" class="h-100">
        @yield('content')
    </div>
</body>

<script src="{{ asset("/vendor/mailweb/app.js") }}" defer></script>

</html>