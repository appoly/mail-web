<?php

namespace Appoly\MailWeb\Livewire;

use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Appoly\MailWeb\Http\Models\MailwebEmail;
use Illuminate\Support\Facades\Notification;
use Appoly\MailWeb\Notifications\MailwebSampleNotification;

class SampleEmailAlert extends Component
{
    #[Validate('required', 'email')]
    public $email = 'example@example.com';

    public function render()
    {
        return view('mailweb::livewire.sample-email-alert');
    }

    public function sendSampleEmail()
    {
        $this->validate();

        Notification::route('mail', $this->email)
            ->notify(new MailwebSampleNotification);

        $this->dispatch('reloadEmails');
    }

    #[Computed]
    public function message()
    {
        return match (true) {
            MailwebEmail::count() == 0 => 'It looks like you are yet to send an email. <br> Check out how mail web works by sending a sample email now!',
            default => 'Send a sample email using the form below'
        };
    }

}
