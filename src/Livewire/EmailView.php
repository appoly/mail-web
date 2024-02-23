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
            $this->email == null => 'Select an email to view it here',
            default => '',
        };
    }
}
