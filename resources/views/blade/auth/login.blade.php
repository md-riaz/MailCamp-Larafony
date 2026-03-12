<?php
$title = 'Login';
ob_start();
$basePath = rtrim(parse_url($_ENV['APP_URL'] ?? getenv('APP_URL') ?: '', PHP_URL_PATH) ?? '', '/');
$componentsPath = dirname(__DIR__, 3) . '/resources/views/blade/components';
?>

<div class="auth-shell">
    <div class="card auth-hero-card w-100 mb-4" style="max-width: 900px; margin: 0 auto;">
        <div class="row g-0 align-items-stretch">
            <div class="col-12 col-lg-6 p-4 p-lg-5 d-flex flex-column justify-content-between">
                <div>
                    <div class="eyebrow mb-3">SMTP-first Campaign Platform</div>
                    <h2 class="display-6 fw-bold mb-3">Operate campaigns with delivery visibility, guardrails, and modern portal UX.</h2>
                    <p class="mb-0" style="color: rgba(255,255,255,0.82) !important;">MailCamp brings campaigns, tracking, bounce intelligence, and safety checks into one operator-friendly control surface.</p>
                </div>
                <div class="small mt-4" style="color: rgba(255,255,255,0.72);">Login to continue into your workspace.</div>
            </div>
            <div class="col-12 col-lg-6 bg-white text-dark p-4 p-lg-5">
                <h2 class="h4 mb-1">Login to MailCamp</h2>
                <p class="text-secondary mb-4">Access your campaign workspace</p>

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
    </div>
</div>

<?php
$content = ob_get_clean();
include dirname(__DIR__, 3) . '/resources/views/blade/layout.php';
