@extends('layouts.app')

@section('title', 'Payment Notification Metrics')

@section('content')
<div class="container">
    <h1 class="my-4">Payment Notification Metrics</h1>

    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Orders</h5>
                    <h2>{{ $totalOrders }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Successful Payments</h5>
                    <h2>{{ $successfulOrders }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5 class="card-title">Pending Payments</h5>
                    <h2>{{ $pendingOrders }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h5 class="card-title">Failed/Expired</h5>
                    <h2>{{ $failedOrders + $expiredOrders }}</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8 mb-4">
            <div class="card">
                <div class="card-header bg-info text-white">
                    Monthly Revenue
                </div>
                <div class="card-body">
                    <canvas id="revenueChart" height="300"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-header bg-info text-white">
                    Payment Summary
                </div>
                <div class="card-body">
                    <h5>Total Revenue</h5>
                    <h2>Rp {{ number_format($totalRevenue, 0, ',', '.') }}</h2>
                    <hr>
                    <h5>Success Rate</h5>
                    <h3>{{ $totalOrders > 0 ? round(($successfulOrders / $totalOrders) * 100) : 0 }}%</h3>
                    <a href="{{ route('admin.payment.logs') }}" class="btn btn-outline-secondary mt-3">View Notification
                        Logs</a>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-info text-white">
            Recent Orders
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Invoice ID</th>
                            <th>Student</th>
                            <th>Course</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentOrders as $order)
                        <tr>
                            <td>{{ $order->invoice_id }}</td>
                            <td>
                                @if($order->student)
                                {{ $order->student->name }}
                                @else
                                <span class="text-muted">Unknown</span>
                                @endif
                            </td>
                            <td>
                                @if($order->course)
                                {{ $order->course->name }}
                                @else
                                <span class="text-muted">Unknown</span>
                                @endif
                            </td>
                            <td>Rp {{ number_format($order->amount, 0, ',', '.') }}</td>
                            <td>
                                @if($order->status == 'success')
                                <span class="badge bg-success">Success</span>
                                @elseif($order->status == 'pending')
                                <span class="badge bg-warning">Pending</span>
                                @elseif($order->status == 'failed')
                                <span class="badge bg-danger">Failed</span>
                                @elseif($order->status == 'expired')
                                <span class="badge bg-secondary">Expired</span>
                                @else
                                <span class="badge bg-info">{{ $order->status }}</span>
                                @endif
                            </td>
                            <td>{{ $order->created_at->format('d M Y H:i') }}</td>
                            <td>
                                <form action="{{ route('admin.payment.resend', $order->id) }}" method="POST"
                                    style="display: inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-primary">Resend Notification</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('revenueChart').getContext('2d');
        const monthlyLabels = @json($monthlyLabels);
        const monthlyData = @json($monthlyData);
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: monthlyLabels,
                datasets: [{
                    label: 'Monthly Revenue (Rp)',
                    data: monthlyData,
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 2,
                    tension: 0.4
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            // Include a thousand separator
                            callback: function(value, index, values) {
                                return 'Rp ' + value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += 'Rp ' + context.parsed.y.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                                }
                                return label;
                            }
                        }
                    }
                }
            }
        });
    });
</script>
@endsection