@extends('mailweb::layouts.app')
@section('content')
        <div class="flex flex-col gap-5 mt-4 lg:flex-row md:mx-auto">
            <livewire:mailweb::email-list-view />
            <div class="flex flex-col w-full gap-4">
                <livewire:mailweb::email-view :show-only="false" />
                @if(config('MailWeb.MAILWEB_SAMPLE_SECTION'))
                <livewire:mailweb::sample-email-alert />
                @endif
            </div>
        </div>

@endsection
