@extends('mailweb::layouts.app')
@section('content')
    <div class="py-8 bg-white dark:bg-gray-900 min-h-screen sm:px-8">
        <div class="header md:max-w-[100rem] md:mx-auto">
            <h1 class="text-3xl font-bold text-gray-700 dark:text-gray-200">
                Mail Web
            </h1>
        </div>
        <div class="flex flex-col gap-4 mt-4 lg:flex-row md:max-w-[100rem] md:mx-auto">
            <livewire:mailweb::email-list-view />
            <livewire:mailweb::email-view />
        </div>
    </div>
@endsection
