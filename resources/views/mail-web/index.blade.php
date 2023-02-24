@extends('mail-web::layouts.app')
@section('content')
<mail-web :toolbar={{ json_encode(config('MailWeb.MAILWEB_TOOLBAR')) }}></mail-web>

@endsection