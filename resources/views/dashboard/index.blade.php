@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<h1>Dashboard</h1>

<div class="stats">
    <div class="stat-card">
        <h3>Total Campaigns</h3>
        <div class="stat-value">{{ $stats['total_campaigns'] }}</div>
    </div>
    <div class="stat-card">
        <h3>Active Campaigns</h3>
        <div class="stat-value">{{ $stats['active_campaigns'] }}</div>
    </div>
    <div class="stat-card">
        <h3>Total Recipients</h3>
        <div class="stat-value">{{ $stats['total_recipients'] }}</div>
    </div>
    <div class="stat-card">
        <h3>Total Templates</h3>
        <div class="stat-value">{{ $stats['total_templates'] }}</div>
    </div>
</div>

<div class="card">
    <h2>Recent Campaigns</h2>
    @if(count($recent_campaigns) > 0)
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Status</th>
                    <th>Recipients</th>
                    <th>Sent</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recent_campaigns as $campaign)
                <tr>
                    <td>{{ $campaign->name }}</td>
                    <td>{{ $campaign->status }}</td>
                    <td>{{ $campaign->total_recipients }}</td>
                    <td>{{ $campaign->sent_count }}</td>
                    <td>{{ $campaign->created_at }}</td>
                    <td><a href="/campaigns/{{ $campaign->id }}" class="btn">View</a></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>No campaigns yet. <a href="/campaigns/create">Create your first campaign</a></p>
    @endif
</div>
@endsection
