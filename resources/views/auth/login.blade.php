@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="card" style="max-width: 500px; margin: 100px auto;">
    <h2 style="margin-bottom: 20px;">Login to MailCamp</h2>
    
    <form method="POST" action="/login">
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="{{ $email ?? '' }}" required>
        </div>
        
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>
        
        <button type="submit" class="btn">Login</button>
        <p style="margin-top: 15px;">
            Don't have an account? <a href="/register">Register here</a>
        </p>
    </form>
</div>
@endsection
