@extends('layouts.app')

@section('title', 'Email Templates')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h1>Email Templates</h1>
    <a href="/templates/create" class="btn btn-success">Create New Template</a>
</div>

<div class="card">
    @if(count($templates) > 0)
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Subject</th>
                    <th>Variables</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($templates as $template)
                <tr>
                    <td>{{ $template->name }}</td>
                    <td>{{ $template->subject }}</td>
                    <td>{{ implode(', ', $template->parseVariables()) }}</td>
                    <td>{{ $template->is_active ? 'Active' : 'Inactive' }}</td>
                    <td>{{ $template->created_at }}</td>
                    <td>
                        <a href="/templates/{{ $template->id }}" class="btn">Edit</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>No templates yet. <a href="/templates/create">Create your first template</a></p>
    @endif
</div>
@endsection
