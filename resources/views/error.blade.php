@extends('mailweb::layouts.app')
@section('content')
    <div class="flex flex-col gap-4 mt-4 lg:flex-row md:max-w-[100rem] md:mx-auto">
        Failed to download attachment, refer to logs for details
        {{ $errorMessage }}
    </div>
@endsection
