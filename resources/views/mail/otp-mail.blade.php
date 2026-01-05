
@extends('mail.layout')

@section('content')
<div class="email-content">

    <p>Dear <strong>{{ $data['name'] }}</strong>,</p>

    <p>
        {{ 'Please use the following verification code to complete your process:' }}
    </p>

    {{-- ✅ OTP Code Box --}}
    <div class="code-display">
        <div class="code">{{ $data['code'] }}</div>
    </div>

    <p class="otp-expire-text">
        This code is valid for {{ 5 }} minutes
    </p>

    <div class="divider"></div>

    <p class="otp-warning-text">
        If you did not request this code, please ignore this email or contact our support team if you have concerns.
    </p>

</div>
@endsection
