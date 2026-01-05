@extends('mail.layout')

@section('content')
<div class="email-content">
    <h2 style="margin-bottom: 10px;">
        {{ $title }}
    </h2>
    <p>Dear <strong>{{ $name }}</strong>,</p>
    <p>{!! nl2br(e($body)) !!}</p>

    @if(!empty($actionUrl))
        <div style="text-align: center; margin-top: 20px;">
            <a href="{{ $actionUrl }}" class="btn-primary">
                {{ $actionText ?? 'Open →' }}
            </a>
        </div>
    @endif

</div>
@endsection
