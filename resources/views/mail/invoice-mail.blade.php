@extends('mail.layout')

@section('content')
<div class="email-content">

    <p>Dear <strong>{{ $data->user?->name ?? 'Customer' }}</strong>,</p>

    <p>
        Thank you for your order with <strong>Aaleyat</strong>.
        Below are the details of your invoice.
    </p>

    {{-- ✅ Invoice Summary Box --}}
    <div class="details-box">
        <p><strong>Order Number:</strong> {{ $data->number }}</p>
        <p><strong>Date:</strong> {{ $data->last_status_time?->format('Y-m-d h:i A') }}</p>
        <p><strong>Status:</strong> {{ $data->status->value }}</p>
        <p><strong>Total Amount:</strong> {{ number_format($data->payment->total, 2) }}</p>
    </div>

    <p style="text-align:center;color:#718096;font-size:14px;">
        This amount includes all applicable taxes and fees.
    </p>

    <div class="divider"></div>

    <p>
        You can view your full invoice and order details from your account dashboard.
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
