<?php 
$title = 'Register';
ob_start(); 
$basePath = rtrim(parse_url($_ENV['APP_URL'] ?? getenv('APP_URL') ?: '', PHP_URL_PATH) ?? '', '/');
?>

<div class="auth-shell">
    <div class="card w-100" style="max-width: 520px; margin: 0 auto;">
        <h2 class="h4 mb-1">Register Your Organization</h2>
        <p class="text-secondary mb-4">Create an account to start sending campaigns</p>

        <?php if (!empty($error)) : ?>
            <div class="alert alert-danger" role="alert" aria-live="assertive" id="form-error">
                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= $basePath ?>/register" class="row g-3">
            <div class="col-12">
                <label for="name" class="form-label">Full Name</label>
                <input type="text" class="form-control" id="name" name="name" required autocomplete="name" aria-describedby="<?php echo !empty($error) ? 'form-error' : '' ?>" value="<?= htmlspecialchars($name ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>

            <div class="col-12">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" class="form-control" id="email" name="email" required autocomplete="email" value="<?= htmlspecialchars($email ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>

            <div class="col-12">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required autocomplete="new-password">
            </div>

            <div class="col-12">
                <label for="organization_name" class="form-label">Organization Name</label>
                <input type="text" class="form-control" id="organization_name" name="organization_name" required autocomplete="organization" value="<?= htmlspecialchars($organization_name ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>

            <div class="col-12 d-grid">
                <button type="submit" class="btn btn-primary">Register</button>
            </div>
        </form>

        <p class="text-center mt-4 mb-0">
            Already have an account? <a href="<?= $basePath ?>/login">Login here</a>
        </p>
    </div>
</div>

<?php 
$content = ob_get_clean(); 
include dirname(__DIR__, 3) . '/resources/views/blade/layout.php'; 
?>