@extends('layouts.app')

@section('title', 'Create Campaign')

@section('content')
<h1>Create New Campaign</h1>

<div class="card">
    <form method="POST" action="/campaigns">
        <div class="form-group">
            <label for="name">Campaign Name</label>
            <input type="text" id="name" name="name" required>
        </div>
        
        <div class="form-group">
            <label for="template_id">Email Template</label>
            <select id="template_id" name="template_id" required>
                <option value="">Select a template</option>
                @foreach($templates as $template)
                    <option value="{{ $template->id }}">{{ $template->name }}</option>
                @endforeach
            </select>
        </div>
        
        <button type="submit" class="btn">Create Campaign</button>
        <a href="/campaigns" class="btn">Cancel</a>
    </form>
</div>
@endsection
