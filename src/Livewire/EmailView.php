<?php

namespace Appoly\MailWeb\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
use Appoly\MailWeb\Http\Models\MailwebEmail;

class EmailView extends Component
{
    public $email;

    public function render()
    {
        if (! $this->email) {
            $this->email = MailwebEmail::latest()->first();
        }

        return view('mailweb::livewire.email-view');
    }

    #[On('viewEmail')]
    public function viewEmail($emailId)
    {
        $email = MailwebEmail::find($emailId);
        $this->email = $email;
    }
}
