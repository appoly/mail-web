<?php

namespace Appoly\MailWeb\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MailWebRequest extends FormRequest
{
    public function rules()
    {
        return [
            'from' => 'nullable|date',
            'to' => 'nullable|date',
            'search' => 'nullable|string',
        ];
    }
}