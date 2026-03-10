<?php 
$title = 'Register';
ob_start(); 
?>

<div class="auth-shell">
<div class="card" style="max-width: 500px; width: 100%; margin: 0 auto;">
    <h2>Register Your Organization</h2>
    <p style="color: #7f8c8d; margin-bottom: 20px;">Create an account to start sending campaigns</p>
    <?php if (!empty($error)) : ?>
        <div class="alert alert-error" role="alert" aria-live="assertive" id="form-error" style="margin-bottom:16px;color:#b00020;">
            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>
    
    <form method="POST" action="<?= rtrim(parse_url($_ENV['APP_URL'] ?? getenv('APP_URL') ?: '', PHP_URL_PATH) ?? '', '/') ?>/register">
        <div class="form-group">
            <label for="name">Full Name</label>
            <input type="text" id="name" name="name" required autocomplete="name" aria-describedby="<?php echo !empty($error) ? 'form-error' : '' ?>" value="<?= htmlspecialchars($name ?? '', ENT_QUOTES, 'UTF-8') ?>">
        </div>
        
        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" required autocomplete="email" value="<?= htmlspecialchars($email ?? '', ENT_QUOTES, 'UTF-8') ?>">
        </div>
        
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required autocomplete="new-password">
        </div>
        
        <div class="form-group">
            <label for="organization_name">Organization Name</label>
            <input type="text" id="organization_name" name="organization_name" required autocomplete="organization" value="<?= htmlspecialchars($organization_name ?? '', ENT_QUOTES, 'UTF-8') ?>">
        </div>
        
        <div class="form-group">
            <button type="submit" class="btn">Register</button>
        </div>
    </form>
    
    <p style="text-align: center; margin-top: 20px;">
        Already have an account? <a href="<?= rtrim(parse_url($_ENV['APP_URL'] ?? getenv('APP_URL') ?: '', PHP_URL_PATH) ?? '', '/') ?>/login">Login here</a>
    </p>
</div>
</div>

<?php 
$content = ob_get_clean(); 
include dirname(__DIR__, 3) . '/resources/views/blade/layout.php'; 
?>
