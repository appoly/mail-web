@extends('mailweb::layouts.app')
@section('content')
    <div class="flex flex-col gap-4 mt-4 lg:flex-row md:max-w-[100rem] md:mx-auto">
        <livewire:mailweb::email-view :email="$email" />
    </div>
@endsection
