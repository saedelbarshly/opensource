@extends('mail.layout')

@section('content')
<div class="email-content">

    <p>Dear <strong>{{ $data->name}}</strong>,</p>

    <p>
        {!! nl2br(e('We regret to inform you that your company registration with Aaleyat has been rejected at this time.')) !!}
    </p>

    <div class="warning-box">
        <strong>Reason for Rejection:</strong>
        <p>
            {{ $data->credential->reason  ?? 'The submitted documents did not meet our verification requirements. Please ensure all documents are clear, valid, and match the registration information provided.' }}
        </p>
    </div>

    <p>
        You can review your submitted information and reapply once you have addressed the issues mentioned above.
        Our support team is here to help guide you through the process.
    </p>

    @if(!empty($actionUrl))
        <div style="text-align: center;">
            <a href="{{ $actionUrl }}" class="btn-primary">
                {{ $actionText ?? 'Contact Support →' }}
            </a>
        </div>
    @endif

</div>
@endsection

