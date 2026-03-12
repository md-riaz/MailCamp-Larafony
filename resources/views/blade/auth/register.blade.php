<?php
$title = 'Register';
ob_start();
$basePath = rtrim(parse_url($_ENV['APP_URL'] ?? getenv('APP_URL') ?: '', PHP_URL_PATH) ?? '', '/');
$componentsPath = dirname(__DIR__, 3) . '/resources/views/blade/components';
?>

<div class="auth-shell">
    <div class="card auth-hero-card w-100 mb-4" style="max-width: 980px; margin: 0 auto;">
        <div class="row g-0 align-items-stretch">
            <div class="col-12 col-lg-5 p-4 p-lg-5 d-flex flex-column justify-content-between">
                <div>
                    <div class="eyebrow mb-3">Create Workspace</div>
                    <h2 class="display-6 fw-bold mb-3">Set up a campaign workspace built for SMTP delivery, safety checks, and observability.</h2>
                    <p class="mb-0" style="color: rgba(255,255,255,0.82) !important;">Register your organization once, configure SMTP properly, and manage campaigns from a portal that treats delivery as infrastructure.</p>
                </div>
                <div class="small mt-4" style="color: rgba(255,255,255,0.72);">This is the starting point for your operational mail stack.</div>
            </div>
            <div class="col-12 col-lg-7 bg-white text-dark p-4 p-lg-5">
                <h2 class="h4 mb-1">Register Your Organization</h2>
                <p class="text-secondary mb-4">Create an account to start sending campaigns</p>

        <?php
        $message = $error ?? '';
        $type = 'danger';
        $id = 'form-error';
        include $componentsPath . '/flash-alert.blade.php';
        ?>

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
    </div>
</div>

<?php
$content = ob_get_clean();
include dirname(__DIR__, 3) . '/resources/views/blade/layout.php';
