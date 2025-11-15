<?php 
$title = 'Login';
ob_start(); 
?>

<div class="card" style="max-width: 500px; margin: 100px auto;">
    <h2>Login to MailCamp</h2>
    <p style="color: #7f8c8d; margin-bottom: 20px;">Multi-tenant Email Campaign Manager</p>
    <?php if (!empty($error)) : ?>
        <div class="alert alert-error" role="alert" aria-live="assertive" id="form-error" style="margin-bottom:16px;color:#b00020;">
            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>
    
    <form method="POST" action="/login">
        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" required autocomplete="username" aria-describedby="<?php echo !empty($error) ? 'form-error' : '' ?>" value="<?= htmlspecialchars($email ?? 'admin@demo.example.com', ENT_QUOTES, 'UTF-8') ?>">
        </div>
        
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required autocomplete="current-password" value="admin123">
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
