<?php

namespace Appoly\MailWeb\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Appoly\MailWeb\Http\Models\MailwebEmail;

class EmailListView extends Component
{
    use WithPagination;

    public $searchQuery = '';

    public function render()
    {
        return view('mailweb::livewire.email-list-view', [
            'emails' => MailwebEmail::paginate(15),
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
}
