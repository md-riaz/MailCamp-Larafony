<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'MailCamp' ?> - MailCamp</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        :root {
            --bg: #f6f8fc;
            --surface: #ffffff;
            --surface-soft: #f1f5ff;
            --text: #0f172a;
            --muted: #64748b;
            --primary: #2563eb;
            --primary-2: #4f46e5;
            --danger: #dc2626;
            --success: #16a34a;
            --border: #e2e8f0;
            --shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
            --radius: 14px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: Inter, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: var(--text);
            background: radial-gradient(circle at 0% 0%, #eef2ff 0, var(--bg) 45%);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        nav {
            position: sticky;
            top: 0;
            z-index: 10;
            border-bottom: 1px solid rgba(148, 163, 184, 0.2);
            backdrop-filter: blur(8px);
            background: rgba(15, 23, 42, 0.85);
            color: #fff;
        }

        nav .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            padding-top: 14px;
            padding-bottom: 14px;
        }

        nav h1 {
            font-size: 1.2rem;
            letter-spacing: 0.3px;
            padding: 8px 12px;
            border-radius: 999px;
            background: rgba(37, 99, 235, 0.2);
            border: 1px solid rgba(96, 165, 250, 0.35);
        }

        nav ul {
            list-style: none;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        nav a {
            color: #e2e8f0;
            text-decoration: none;
            padding: 8px 12px;
            border-radius: 10px;
            transition: all 0.2s ease;
        }

        nav a:hover {
            color: #fff;
            background: rgba(148, 163, 184, 0.2);
            text-decoration: none;
        }

        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 24px;
            box-shadow: var(--shadow);
            margin-bottom: 20px;
        }

        .portal-hero {
            position: relative;
            overflow: hidden;
            background:
                radial-gradient(circle at top right, rgba(79, 70, 229, 0.18), transparent 30%),
                linear-gradient(135deg, #0f172a 0%, #1e293b 42%, #1d4ed8 100%);
            color: #fff;
            border: 1px solid rgba(96, 165, 250, 0.18);
            box-shadow: 0 18px 45px rgba(15, 23, 42, 0.18);
        }

        .portal-hero::after {
            content: '';
            position: absolute;
            inset: auto -30px -30px auto;
            width: 180px;
            height: 180px;
            background: radial-gradient(circle, rgba(255,255,255,0.18), transparent 60%);
            pointer-events: none;
        }

        .portal-hero .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 10px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.12);
            color: rgba(255,255,255,0.9);
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .portal-hero h1,
        .portal-hero h2,
        .portal-hero h3,
        .portal-hero p,
        .portal-hero .text-secondary,
        .portal-hero .text-muted {
            color: #fff !important;
        }

        .portal-grid {
            display: grid;
            gap: 16px;
        }

        .portal-grid.portal-grid-4 {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }

        .portal-metric {
            background: linear-gradient(180deg, rgba(255,255,255,0.88), rgba(248,250,252,0.98));
            border: 1px solid rgba(148, 163, 184, 0.22);
            border-radius: 16px;
            padding: 18px;
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.07);
        }

        .portal-metric .metric-label {
            color: var(--muted);
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin-bottom: 8px;
        }

        .portal-metric .metric-value {
            font-size: 30px;
            font-weight: 800;
            line-height: 1;
            color: var(--text);
        }

        .portal-metric .metric-note {
            margin-top: 8px;
            color: var(--muted);
            font-size: 12px;
        }

        .portal-section-title {
            font-size: 0.78rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #475569;
            margin-bottom: 12px;
        }

        .portal-surface-soft {
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
            border: 1px solid #e2e8f0;
            border-radius: 16px;
        }

        .portal-stack > * + * {
            margin-top: 12px;
        }

        .card h2 { margin-bottom: 10px; color: var(--text); }
        .form-group { margin-bottom: 18px; }

        label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            color: #334155;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="number"],
        textarea,
        select {
            width: 100%;
            padding: 11px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            font-size: 14px;
            background: #fff;
            transition: all 0.2s ease;
        }

        input:focus, textarea:focus, select:focus {
            outline: none;
            border-color: #60a5fa;
            box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.25);
        }

        textarea { min-height: 120px; resize: vertical; }

        button, .btn {
            background: linear-gradient(135deg, var(--primary), var(--primary-2));
            color: #fff;
            padding: 10px 16px;
            border: 0;
            border-radius: 10px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: transform 0.15s ease, filter 0.15s ease;
        }

        button:hover, .btn:hover { transform: translateY(-1px); filter: brightness(1.05); text-decoration: none; }

        .btn-secondary { background: #475569; }
        .btn-danger { background: var(--danger); }
        .btn-success { background: var(--success); }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            overflow: hidden;
            border: 1px solid var(--border);
            border-radius: 12px;
            margin-top: 16px;
            background: #fff;
        }

        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #f1f5f9; }
        th { background: var(--surface-soft); font-weight: 700; color: #1e293b; }
        tr:hover td { background: #f8fafc; }

        .alert {
            padding: 14px 16px;
            border-radius: 14px;
            margin-bottom: 18px;
            border: 1px solid transparent;
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.05);
        }

        .alert-success { background: #ecfdf3; color: #166534; border-color: #bbf7d0; }
        .alert-error, .alert-danger { background: #fef2f2; color: #991b1b; border-color: #fecaca; }
        .alert-info { background: #eff6ff; color: #1e40af; border-color: #bfdbfe; }
        .alert-warning { background: #fffbeb; color: #92400e; border-color: #fde68a; }

        a { color: var(--primary); text-decoration: none; }
        a:hover { text-decoration: underline; }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: var(--surface);
            padding: 18px;
            border-radius: 14px;
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
        }

        .stat-card h3 { color: var(--muted); font-size: 13px; margin-bottom: 8px; }

        .stat-card .number,
        .stat-card .stat-value {
            font-size: 30px;
            font-weight: 700;
            color: var(--text);
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            gap: 12px;
            margin: 8px 0 18px;
        }

        .page-title { font-size: 1.7rem; font-weight: 800; letter-spacing: -0.02em; }
        .page-subtitle { color: var(--muted); margin-top: 4px; }
        .toolbar { display: flex; gap: 10px; flex-wrap: wrap; }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            text-transform: capitalize;
            border: 1px solid transparent;
        }

        .badge-success { background: #ecfdf3; color: #166534; border-color: #bbf7d0; }
        .badge-warning { background: #fffbeb; color: #92400e; border-color: #fde68a; }
        .badge-info { background: #eff6ff; color: #1d4ed8; border-color: #bfdbfe; }
        .badge-danger { background: #fef2f2; color: #991b1b; border-color: #fecaca; }
        .badge-muted { background: #f8fafc; color: #334155; border-color: #cbd5e1; }

        .empty-state {
            text-align: center;
            padding: 30px 14px;
            color: var(--muted);
        }

        .auth-shell {
            min-height: calc(100vh - 40px);
            display: grid;
            place-items: center;
        }

        .auth-hero-card {
            position: relative;
            overflow: hidden;
            background: linear-gradient(145deg, #0f172a 0%, #1d4ed8 100%);
            color: #fff;
            border: 1px solid rgba(96, 165, 250, 0.24);
        }

        .auth-hero-card p,
        .auth-hero-card h1,
        .auth-hero-card h2,
        .auth-hero-card h3,
        .auth-hero-card .text-secondary {
            color: #fff !important;
        }

        .nav-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 999px;
            background: rgba(148, 163, 184, 0.12);
            border: 1px solid rgba(148, 163, 184, 0.14);
            color: #e2e8f0;
        }

        .nav-pill strong {
            color: #fff;
        }

        @media (max-width: 992px) {
            .portal-grid.portal-grid-4 {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 768px) {
            .container { padding: 14px; }
            .card { padding: 16px; }
            .page-header { flex-direction: column; align-items: flex-start; }
            .page-title { font-size: 1.4rem; }
            .portal-grid.portal-grid-4 {
                grid-template-columns: 1fr;
            }
            table { display: block; overflow-x: auto; white-space: nowrap; }
        }
    </style>
</head>
<body>
    <?php $basePath = rtrim(parse_url($_ENV['APP_URL'] ?? getenv('APP_URL') ?: '', PHP_URL_PATH) ?? '', '/'); ?>
    <?php if (isset($user)): ?>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <div class="d-flex align-items-center gap-3 flex-wrap">
                <a class="navbar-brand fw-semibold mb-0" href="<?= $basePath ?>/">📧 MailCamp</a>
                <div class="nav-pill small">
                    <span>Workspace</span>
                    <strong><?= htmlspecialchars($user->organization?->name ?? 'MailCamp', ENT_QUOTES, 'UTF-8') ?></strong>
                </div>
            </div>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mailcampNav" aria-controls="mailcampNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mailcampNav">
                <ul class="navbar-nav ms-auto gap-lg-2 align-items-lg-center">
                    <li class="nav-item"><a class="nav-link" href="<?= $basePath ?>/">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= $basePath ?>/campaigns">Campaigns</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= $basePath ?>/templates">Templates</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= $basePath ?>/smtp-settings">SMTP</a></li>
                    <li class="nav-item"><a class="nav-link text-warning" href="<?= $basePath ?>/logout">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <?php endif; ?>

    <main class="container py-3 py-lg-4">
        <?= $content ?? '' ?>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <?= $scripts ?? '' ?>
</body>
</html>
