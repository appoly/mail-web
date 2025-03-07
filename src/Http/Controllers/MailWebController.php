<?php

namespace Appoly\MailWeb\Http\Controllers;

use Illuminate\View\View;
use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Appoly\MailWeb\Http\Models\MailwebEmail;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller for handling Mail Web related requests.
 */
class MailWebController
{
    /**
     * Check if the current user has permission to access Mail Web.
     *
     * @param  string  $message  Custom error message (optional)
     */
    private function authorizeMailWebAccess(string $message = 'Unauthorized access to Mail Web'): void
    {
        if (Gate::denies('view-mailweb', auth()->user())) {
            abort(Response::HTTP_FORBIDDEN, $message);
        }
    }

    /**
     * Display the Mail Web dashboard.
     */
    public function index(): View
    {
        $this->authorizeMailWebAccess();

        return view('mailweb::dashboard');
    }

    /**
     * Fetch emails for the dashboard with pagination.
     */
    public function fetchEmails(\Illuminate\Http\Request $request): JsonResponse
    {
        $this->authorizeMailWebAccess();

        $perPage = $request->input('per_page', 25);
        $page = $request->input('page', 1);
        $search = $request->input('search');

        $emails = MailwebEmail::query()
            ->when($search, fn ($q) => $q->search($search))
            ->orderBy('created_at', 'desc')
            ->select([
                'id', 'from', 'to', 'cc', 'bcc', 'subject', 'body_text',
                'read', 'share_enabled', 'created_at', 'updated_at',
            ])
            ->paginate($perPage, ['*'], 'page', $page)
            ->through(function ($email) {
                return [
                    'id' => $email->id,
                    'from' => $email->from,
                    'to' => $email->to,
                    'cc' => $email->cc,
                    'bcc' => $email->bcc,
                    'subject' => $email->subject,
                    'body_text' => Str::of($email->body_text)->limit(60),
                    'read' => $email->read,
                    'share_enabled' => $email->share_enabled,
                    'created_at' => $email->created_at,
                    'updated_at' => $email->updated_at,
                ];
            });

        return response()->json($emails);
    }

    /**
     * Show a specific email.
     */
    public function show(MailwebEmail $mailwebEmail): View
    {
        $this->authorizeMailWebAccess();

        abort_if(
            ! $mailwebEmail->share_enabled,
            Response::HTTP_FORBIDDEN,
            'This email is not shared or available for viewing'
        );

        return view('mailweb::email', [
            'email' => $mailwebEmail,
        ]);
    }

    /**
     * Fetch a single email with full content.
     */
    public function fetchEmail(string $id): JsonResponse
    {
        $this->authorizeMailWebAccess();

        $email = MailwebEmail::with('attachments')->findOrFail($id);

        // Mark the email as read
        if (! $email->read) {
            $email->read = 1;
            $email->save();
        }

        return response()->json($email);
    }
}
