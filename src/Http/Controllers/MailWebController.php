<?php

namespace Appoly\MailWeb\Http\Controllers;

class MailWebController
{
    public function index()
    {
        return view('mailweb::dashboard');
    }
}
