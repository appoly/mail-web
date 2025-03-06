<?php

namespace Appoly\MailWeb\Http\Controllers;

use Illuminate\View\View;
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
     * Fetch all emails for the dashboard.
     */
    public function fetchEmails(): JsonResponse
    {
        $this->authorizeMailWebAccess();

        return response()->json(MailwebEmail::all());
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
}
