<?php

namespace Appoly\MailWeb\Http\Controllers;

use Illuminate\Support\Facades\Gate;

class MailWebController
{
    public function index()
    {
        // TODO - Gate
        // if (Gate::denies('view-mailweb', auth()->user())) {
        //     return abort('403');
        // }

        return view('mailweb::dashboard');
    }

}
