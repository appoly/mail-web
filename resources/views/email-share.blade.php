<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $email->subject }} - Shared Email</title>
    <link rel="stylesheet" href="{{ asset('/vendor/mailweb/mailweb.css') }}">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }
        .email-card {
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            overflow: hidden;
        }
        .email-header {
            padding: 1.5rem;
            border-bottom: 1px solid #e2e8f0;
        }
        .email-subject {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.75rem;
            color: #0f172a;
        }
        .email-meta {
            font-size: 0.875rem;
            color: #64748b;
            margin-bottom: 0.5rem;
        }
        .email-meta-label {
            font-weight: 500;
            display: inline-block;
            width: 3.5rem;
        }
        .email-content {
            padding: 0;
        }
        .email-content iframe {
            width: 100%;
            height: 600px;
            border: none;
        }
        .email-footer {
            padding: 1rem 1.5rem;
            font-size: 0.75rem;
            color: #94a3b8;
            text-align: center;
            border-top: 1px solid #e2e8f0;
        }
        @media (max-width: 640px) {
            .container {
                padding: 1rem 0.5rem;
            }
            .email-header {
                padding: 1rem;
            }
            .email-subject {
                font-size: 1.25rem;
            }
            .email-content iframe {
                height: 400px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="email-card">
            <div class="email-header">
                <h1 class="email-subject">{{ $email->subject }}</h1>
                <div class="email-meta">
                    <div>
                        <span class="email-meta-label">From:</span>
                        @foreach($email->from as $from)
                            {{ $from['name'] ?? '' }} &lt;{{ $from['address'] }}&gt;{{ !$loop->last ? ', ' : '' }}
                        @endforeach
                    </div>
                    <div>
                        <span class="email-meta-label">To:</span>
                        @foreach($email->to as $to)
                            {{ $to['name'] ?? '' }} &lt;{{ $to['address'] }}&gt;{{ !$loop->last ? ', ' : '' }}
                        @endforeach
                    </div>
                    @if(count($email->cc ?? []) > 0)
                    <div>
                        <span class="email-meta-label">CC:</span>
                        @foreach($email->cc as $cc)
                            {{ $cc['name'] ?? '' }} &lt;{{ $cc['address'] }}&gt;{{ !$loop->last ? ', ' : '' }}
                        @endforeach
                    </div>
                    @endif
                    <div>
                        <span class="email-meta-label">Date:</span>
                        {{ $email->created_at->format('F j, Y, g:i a') }}
                    </div>
                </div>
            </div>
            <div class="email-content">
                <iframe id="email-iframe" srcdoc="{{ $email->body_html }}"></iframe>
            </div>
            <div class="email-footer">
                Shared via MailWeb
            </div>
        </div>
    </div>
</body>
</html>
