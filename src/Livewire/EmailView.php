<?php

namespace Appoly\MailWeb\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Computed;
use Appoly\MailWeb\Http\Models\MailwebEmail;

class EmailView extends Component
{
    public $email;

    public function render()
    {
        return view('mailweb::livewire.email-view');
    }

    #[On('viewEmail')]
    public function viewEmail($emailId)
    {
        $email = MailwebEmail::find($emailId);
        $this->email = $email;
    }

    #[Computed]
    public function emptyMessage()
    {
        return match (true) {
            MailwebEmail::count() == 0 => 'No emails found',
            $this->email == null => 'Select an email to view it here',
            default => '',
        };
    }

    #[Computed]
    public function emptyIcon()
    {
        return match (true) {
            MailwebEmail::count() == 0 => '<svg xmlns="http://www.w3.org/2000/svg"  viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"
                    width="5rem" height="5rem"
                    style="margin-left: auto; margin-right: auto;">
                    <path d="M17.7 7.7a2.5 2.5 0 1 1 1.8 4.3H2" />
                    <path d="M9.6 4.6A2 2 0 1 1 11 8H2" />
                    <path d="M12.6 19.4A2 2 0 1 0 14 16H2" />
                </svg>',
            $this->email == null => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"
                    width="5rem" height="5rem"
                    style="margin-left: auto; margin-right: auto;">
                    <path d="M22 13V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v12c0 1.1.9 2 2 2h9" />
                    <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7" />
                    <path d="m17 17 4 4" />
                    <path d="m21 17-4 4" />
                </svg>',
            default => '',
        };
    }

    #[On('reloadEmails')]
    public function reload()
    {
        $this->email = null;
    }
}
