@extends('layouts.app')

@section('title', 'Payment Notification Logs')

@section('content')
<div class="container">
    <h1 class="my-4">Payment Notification Logs</h1>

    <div class="card">
        <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
            <span>WhatsApp Notification Logs</span>
            <a href="{{ route('admin.payment.metrics') }}" class="btn btn-sm btn-light">Back to Metrics</a>
        </div>
        <div class="card-body">
            @if(count($notificationLogs) > 0)
            <div class="table-responsive">
                <table class="table table-striped table-sm">
                    <thead>
                        <tr>
                            <th>Date/Time</th>
                            <th>Message</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($notificationLogs as $log)
                        <tr>
                            <td style="white-space: nowrap;">
                                @php
                                $matches = [];
                                preg_match('/^\[(.*?)\]/', $log, $matches);
                                echo $matches[1] ?? '';
                                @endphp
                            </td>
                            <td>{{ $log }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="alert alert-info">No notification logs found.</div>
            @endif
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header bg-info text-white">
            Notification Test Tools
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="card mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Run Payment Notification Tests</h5>
                            <p class="card-text">Test all payment notification types (success, pending, failed,
                                expired).</p>
                            <a href="{{ route('admin.payment.test') }}" class="btn btn-primary">Run Tests</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">WhatsApp API Status</h5>
                            <p class="card-text">Check the status of the WhatsApp API connection.</p>
                            <a href="{{ route('admin.whatsapp.status') }}" class="btn btn-secondary">Check Status</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection