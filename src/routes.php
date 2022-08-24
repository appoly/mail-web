<?php

use Illuminate\Support\Facades\Route;

Route::get('/mailweb', 'Appoly\MailWeb\Http\Controllers\MailWebController@index');
Route::get('/mailweb/emails', 'Appoly\MailWeb\Http\Controllers\MailWebController@get');
