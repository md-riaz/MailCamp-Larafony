<?php
$title = 'Login';
ob_start();
$basePath = rtrim(parse_url($_ENV['APP_URL'] ?? getenv('APP_URL') ?: '', PHP_URL_PATH) ?? '', '/');
$componentsPath = dirname(__DIR__, 3) . '/resources/views/blade/components';
?>

<div class="auth-shell">
    <div class="card w-100" style="max-width: 520px; margin: 0 auto;">
        <h2 class="h4 mb-1">Login to MailCamp</h2>
        <p class="text-secondary mb-4">Multi-tenant Email Campaign Manager</p>

        <?php
        $message = $error ?? '';
        $type = 'danger';
        $id = 'form-error';
        include $componentsPath . '/flash-alert.blade.php';
        ?>

        <form method="POST" action="<?= $basePath ?>/login" class="row g-3">
            <div class="col-12">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" class="form-control" id="email" name="email" required autocomplete="username" aria-describedby="<?php echo !empty($error) ? 'form-error' : '' ?>" value="<?= htmlspecialchars($email ?? 'admin@example.com', ENT_QUOTES, 'UTF-8') ?>">
            </div>

            <div class="col-12">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required autocomplete="current-password" value="password">
            </div>

            <div class="col-12 d-grid">
                <button type="submit" class="btn btn-primary">Login</button>
            </div>
        </form>

        <p class="text-center mt-4 mb-0">
            Don't have an account? <a href="<?= $basePath ?>/register">Register here</a>
        </p>
    </div>
</div>

<?php
$content = ob_get_clean();
include dirname(__DIR__, 3) . '/resources/views/blade/layout.php';
