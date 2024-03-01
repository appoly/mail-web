@extends('mailweb::layouts.app')
@section('content')
    <div class="min-h-screen py-8 text-gray-700 bg-slate-50 dark:bg-gray-900 sm:px-8 dark:text-gray-200">
        <div class="header md:mx-auto">
            <h1 class="text-5xl font-bold">
                Mail Web
            </h1>
        </div>
        <div class="flex flex-col gap-4 mt-4 lg:flex-row md:mx-auto">
            <livewire:mailweb::email-list-view />
            <div class="flex flex-col w-full gap-4">
                <livewire:mailweb::email-view :show-only="false" />
                @if(config('MailWeb.MAILWEB_SAMPLE_SECTION'))
                <livewire:mailweb::sample-email-alert />
                @endif
            </div>
        </div>
    </div>
@endsection
