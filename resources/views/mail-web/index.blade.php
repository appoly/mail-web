@extends('mail-web::layouts.app')
@section('content')

{{print_r(config('MailWeb.MAILWEB_TOOLBAR'))}}

<mail-web :toolbar={{ json_encode(config('MailWeb.MAILWEB_TOOLBAR')) }}></mail-web>

@endsection