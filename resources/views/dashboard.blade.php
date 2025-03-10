<!DOCTYPE html>
<html>
<head>
    <title>MailWeb Dashboard</title>
    <link rel="stylesheet" href="{{ asset('/vendor/mailweb/mailweb.css') }}">
    <script>
        window.mailwebConfig = {
            deleteAllEnabled: {{ config('MailWeb.MAILWEB_DELETE_ALL_ENABLED', false) ? 'true' : 'false' }}
        };
    </script>
</head>
<body>
    <div id="mailweb-dashboard"></div>
    <script src="{{ asset('/vendor/mailweb/mailweb.js') }}"></script>
</body>
</html>