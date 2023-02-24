@extends('mail-web::layouts.app')
@section('content')
<h1>Test that this is updating on dev</h1>
<mail-web :toolbar={{ json_encode(config('MailWeb.MAILWEB_TOOLBAR')) }}></mail-web>

@endsection