@extends('mail.layout')

@section('content')
<div class="email-content">

    <p>Dear <strong>{{$data->name }}</strong>,</p>

    <p>
        We are excited to welcome you as a driver for
        <strong>{{ $data->company->name }}</strong>!
    </p>

    <h2 style="margin: 25px 0 10px; text-align: left;">
        Your Account Details
    </h2>

    <div class="details-box">
        <p><strong>Email:</strong> {{ $data->email }}</p>
        <p><strong>Password:</strong> {{ $data->default_password }}</p>
        <p><strong>Phone:</strong> +{{ $data->phone_code }} {{ $data->phone }}</p>
    </div>

    <p>
        To get started, please log in to your driver dashboard using the credentials above.
        We recommend changing your password after your first login.
    </p>

    @if(!empty($actionUrl))
        <div style="text-align: center;">
            <a href="{{ $actionUrl }}" class="btn-primary">
                {{ $actionText ?? 'Login to Driver App →' }}
            </a>
        </div>
    @endif

</div>
@endsection

