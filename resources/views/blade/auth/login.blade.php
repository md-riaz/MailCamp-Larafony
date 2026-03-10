<?php 
$title = 'Login';
ob_start(); 
?>

<div class="auth-shell">
<div class="card" style="max-width: 500px; width: 100%; margin: 0 auto;">
    <h2>Login to MailCamp</h2>
    <p style="color: #7f8c8d; margin-bottom: 20px;">Multi-tenant Email Campaign Manager</p>
    <?php if (!empty($error)) : ?>
        <div class="alert alert-error" role="alert" aria-live="assertive" id="form-error" style="margin-bottom:16px;color:#b00020;">
            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>
    
    <form method="POST" action="<?= rtrim(parse_url($_ENV['APP_URL'] ?? getenv('APP_URL') ?: '', PHP_URL_PATH) ?? '', '/') ?>/login">
        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" required autocomplete="username" aria-describedby="<?php echo !empty($error) ? 'form-error' : '' ?>" value="<?= htmlspecialchars($email ?? 'admin@example.com', ENT_QUOTES, 'UTF-8') ?>">
        </div>
        
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required autocomplete="current-password" value="password">
        </div>
        
        <div class="form-group">
            <button type="submit" class="btn">Login</button>
        </div>
    </form>
    
    <p style="text-align: center; margin-top: 20px;">
        Don't have an account? <a href="<?= rtrim(parse_url($_ENV['APP_URL'] ?? getenv('APP_URL') ?: '', PHP_URL_PATH) ?? '', '/') ?>/register">Register here</a>
    </p>
</div>
</div>

<?php 
$content = ob_get_clean(); 
include dirname(__DIR__, 3) . '/resources/views/blade/layout.php'; 
?>
