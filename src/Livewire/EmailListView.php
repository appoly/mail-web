<?php

namespace Appoly\MailWeb\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Appoly\MailWeb\Http\Models\MailwebEmail;

class EmailListView extends Component
{
    use WithPagination;

    public $search = '';
    public $hidden = false;
    public $activeEmailId = null;

    public function render()
    {
        return view('mailweb::livewire.email-list-view', [
            'emails' => MailwebEmail::search($this->search)
                ->orderBy('created_at', 'desc')
                ->paginate(),
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
        $this->activeEmailId = $emailId;

        $this->dispatch('viewEmail', emailId: $emailId);
    }

    #[On('reloadEmails')]
    public function reload()
    {
        $this->resetPage();
    }

    public function updating($property, $value)
    {

        if ($property == 'search') {
            $this->resetPage();
        }
    }

    public function paginationView()
    {
        return 'mailweb::livewire.pagination';
    }

    public function toggle()
    {
        $this->hidden = ! $this->hidden;
    }
}
