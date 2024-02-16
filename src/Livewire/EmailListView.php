<?php

namespace Appoly\MailWeb\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Appoly\MailWeb\Http\Models\MailwebEmail;

class EmailListView extends Component
{
    use WithPagination;

    public $search = '';

    public function render()
    {
        return view('mailweb::livewire.email-list-view', [
            'emails' => MailwebEmail::search($this->search)->paginate(10),
        ]);
    }

    public function markAsRead($emailId)
    {
        DB::transaction(function () use ($emailId) {
            $email = MailwebEmail::find($emailId);
            $email->read = true;
            $email->save();
        });
    }

    public function showEmail($emailId)
    {
        $this->markAsRead($emailId);

        $this->dispatch('viewEmail', emailId: $emailId);
    }
}
