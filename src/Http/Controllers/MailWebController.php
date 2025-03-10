<?php

namespace Appoly\MailWeb\Http\Controllers;

use Illuminate\View\View;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Config;
use Appoly\MailWeb\Http\Models\MailwebEmail;
use Illuminate\Support\Facades\Notification;
use Symfony\Component\HttpFoundation\Response;
use Appoly\MailWeb\Notifications\MailwebSampleNotification;

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
                    'share_url' => $email->share_enabled ? route('mailweb.share', $email) : null,
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

        return view('mailweb::email-share', [
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

    /**
     * Toggle the share_enabled status for an email.
     */
    public function toggleShare(string $id): JsonResponse
    {
        $this->authorizeMailWebAccess();

        $email = MailwebEmail::findOrFail($id);
        $email->share_enabled = ! $email->share_enabled;
        $email->save();

        return response()->json([
            'success' => true,
            'share_enabled' => $email->share_enabled,
            'share_url' => $email->share_enabled ? route('mailweb.share', $email) : null,
        ]);
    }

    /**
     * Delete all emails from the system.
     */
    public function deleteAll(): JsonResponse
    {
        $this->authorizeMailWebAccess();

        // Check if delete all feature is enabled
        if (! config('MailWeb.MAILWEB_DELETE_ALL_ENABLED', false)) {
            return response()->json([
                'success' => false,
                'message' => 'Delete all emails feature is disabled',
            ], 403);
        }

        try {
            // Delete all emails
            $count = MailwebEmail::query()->delete();

            return response()->json([
                'success' => true,
                'message' => 'All emails have been deleted successfully',
                'count' => $count,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete all emails',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function delete($id): JsonResponse
    {
        $this->authorizeMailWebAccess();
        try {
            $email = MailwebEmail::findOrFail($id);
            $email->delete();

            return response()->json([
                'success' => true,
                'message' => 'Email deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete email',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function sendTestEmail(Request $request)
    {
        // Temporarily set mail driver to log
        $originalMailDriver = Config::get('mail.default');
        Config::set('mail.default', 'log');

        // Get template ID from request or use random template
        $templateId = $request->input('template_id');

        try {
            // Send notification to example@appoly.co.uk
            Notification::route('mail', 'example@appoly.co.uk')
                ->notify(new MailwebSampleNotification($templateId));

            // Reset mail driver
            Config::set('mail.default', $originalMailDriver);

            return new JsonResponse(['success' => true, 'message' => 'Test email sent to logs']);
        } catch (\Exception $e) {
            // Reset mail driver in case of error
            Config::set('mail.default', $originalMailDriver);

            return new JsonResponse(
                ['success' => false, 'message' => 'Failed to send test email', 'error' => $e->getMessage()],
                500
            );
        }
    }
}
