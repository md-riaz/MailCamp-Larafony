@extends('layouts.app')

@section('title', 'Campaigns')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h1>Email Campaigns</h1>
    <a href="/campaigns/create" class="btn btn-success">Create New Campaign</a>
</div>

<div class="card">
    @if(count($campaigns) > 0)
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Status</th>
                    <th>Template</th>
                    <th>Recipients</th>
                    <th>Sent</th>
                    <th>Failed</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($campaigns as $campaign)
                <tr>
                    <td>{{ $campaign->name }}</td>
                    <td>{{ $campaign->status }}</td>
                    <td>{{ $campaign->template?->name ?? 'N/A' }}</td>
                    <td>{{ $campaign->total_recipients }}</td>
                    <td>{{ $campaign->sent_count }}</td>
                    <td>{{ $campaign->failed_count }}</td>
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
