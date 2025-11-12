<?php 
$title = 'Register';
ob_start(); 
?>

<div class="card" style="max-width: 500px; margin: 100px auto;">
    <h2>Register Your Organization</h2>
    <p style="color: #7f8c8d; margin-bottom: 20px;">Create an account to start sending campaigns</p>
    
    <form method="POST" action="/register">
        <div class="form-group">
            <label for="name">Full Name</label>
            <input type="text" id="name" name="name" required>
        </div>
        
        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" required>
        </div>
        
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>
        
        <div class="form-group">
            <label for="organization_name">Organization Name</label>
            <input type="text" id="organization_name" name="organization_name" required>
        </div>
        
        <div class="form-group">
            <button type="submit" class="btn">Register</button>
        </div>
    </form>
    
    <p style="text-align: center; margin-top: 20px;">
        Already have an account? <a href="/login">Login here</a>
    </p>
</div>

<?php 
$content = ob_get_clean(); 
include __DIR__ . '/../layout.php'; 
?>
