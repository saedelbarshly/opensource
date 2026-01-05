@extends('emails.layout')

@section('content')
<div class="email-content">
    <p>Dear <strong>{{ $data->name }}</strong>,</p>

    <p>Your company registration with <strong>Aaleyat</strong> has been 
        <strong>approved</strong>! 🎉</p>

    <p>You can now access your company dashboard and start leveraging our platform.</p>

    <div style="text-align: center;">
        <a href="{{ route('company.dashboard') }}" class="btn-primary">Go to Dashboard →</a>
    </div>

</div>
@endsection
