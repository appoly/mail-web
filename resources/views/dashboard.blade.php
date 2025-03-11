<!DOCTYPE html>
<html>
<head>
    <title>MailWeb Dashboard</title>
    <link rel="stylesheet" href="{{ asset('/vendor/mailweb/mailweb.css') }}">
    <script>
        window.mailwebConfig = {
            deleteAllEnabled: {{ config('MailWeb.MAILWEB_DELETE_ALL_ENABLED', false) ? 'true' : 'false' }},
            sendSampleButton: {{ config('MailWeb.MAILWEB_SEND_SAMPLE_BUTTON', true) ? 'true' : 'false' }},
            return: {
                appName: "{{ config('MailWeb.MAILWEB_RETURN.APP_NAME') ?? config('app.name') ?? 'App' }}",
                appUrl: "{{ config('MailWeb.MAILWEB_RETURN.APP_URL') ?? '/' }}"
            }
        };
    </script>
</head>
<body>
    <div id="mailweb-dashboard"></div>
    <script src="{{ asset('/vendor/mailweb/mailweb.js') }}"></script>
</body>
</html>