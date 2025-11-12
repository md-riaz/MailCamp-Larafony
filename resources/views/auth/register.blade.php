@extends('layouts.app')

@section('title', 'Register')

@section('content')
<div class="card" style="max-width: 500px; margin: 100px auto;">
    <h2 style="margin-bottom: 20px;">Register for MailCamp</h2>
    
    <form method="POST" action="/register">
        <div class="form-group">
            <label for="name">Full Name</label>
            <input type="text" id="name" name="name" value="{{ $name ?? '' }}" required>
        </div>
        
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="{{ $email ?? '' }}" required>
        </div>
        
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>
        
        <div class="form-group">
            <label for="organization_name">Organization Name</label>
            <input type="text" id="organization_name" name="organization_name" value="{{ $organization_name ?? '' }}" required>
        </div>
        
        <button type="submit" class="btn">Register</button>
        <p style="margin-top: 15px;">
            Already have an account? <a href="/login">Login here</a>
        </p>
    </form>
</div>
@endsection
