@extends('mail-web::layouts.app')
@section('content')

<mail-web :emails="{{json_encode($emails)}}"></mail-web>

@endsection