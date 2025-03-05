<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Mail Web Dashboard</title>
    
    @php
        $manifestPath = public_path('vendor/mailweb/manifest.json');
        $manifest = file_exists($manifestPath) 
            ? json_decode(file_get_contents($manifestPath), true) 
            : [];
        $jsPath = $manifest['resources/js/mailweb.js']['file'] ?? null;
        $cssPath = $manifest['resources/js/mailweb.js']['css'][0] ?? null;
    @endphp
    
    @if($cssPath)
        <link rel="stylesheet" href="{{ asset('vendor/mailweb/' . $cssPath) }}">
    @endif
    
    @if($jsPath)
        <script type="module" src="{{ asset('vendor/mailweb/' . $jsPath) }}"></script>
    @endif
</head>
<body>
    <div id="app">
        <mailweb-dashboard></mailweb-dashboard>
    </div>
</body>
</html>