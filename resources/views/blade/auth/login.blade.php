<?php 
$title = 'Login';
ob_start(); 
?>

<div class="card" style="max-width: 500px; margin: 100px auto;">
    <h2>Login to MailCamp</h2>
    <p style="color: #7f8c8d; margin-bottom: 20px;">Multi-tenant Email Campaign Manager</p>
    
    <form method="POST" action="/login">
        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" required>
        </div>
        
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>
        
        <div class="form-group">
            <button type="submit" class="btn">Login</button>
        </div>
    </form>
    
    <p style="text-align: center; margin-top: 20px;">
        Don't have an account? <a href="/register">Register here</a>
    </p>
</div>

<?php 
$content = ob_get_clean(); 
include __DIR__ . '/../layout.php'; 
?>
