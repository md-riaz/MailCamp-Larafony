<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'MailCamp' ?> - MailCamp</title>
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
            border-radius: 12px;
            margin-bottom: 18px;
            border: 1px solid transparent;
        }

        .alert-success { background: #ecfdf3; color: #166534; border-color: #bbf7d0; }
        .alert-error { background: #fef2f2; color: #991b1b; border-color: #fecaca; }
        .alert-info { background: #eff6ff; color: #1e40af; border-color: #bfdbfe; }

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

        @media (max-width: 768px) {
            nav .container { align-items: flex-start; }
            nav ul { gap: 6px; }
            .container { padding: 14px; }
            .card { padding: 16px; }
            .page-header { flex-direction: column; align-items: flex-start; }
            .page-title { font-size: 1.4rem; }
        }
    </style>
</head>
<body>
    <?php if (isset($user)): ?>
    <nav>
        <div class="container">
            <h1>📧 MailCamp</h1>
            <ul>
                <li><a href="<?= rtrim(parse_url($_ENV['APP_URL'] ?? getenv('APP_URL') ?: '', PHP_URL_PATH) ?? '', '/') ?>/">Dashboard</a></li>
                <li><a href="<?= rtrim(parse_url($_ENV['APP_URL'] ?? getenv('APP_URL') ?: '', PHP_URL_PATH) ?? '', '/') ?>/campaigns">Campaigns</a></li>
                <li><a href="<?= rtrim(parse_url($_ENV['APP_URL'] ?? getenv('APP_URL') ?: '', PHP_URL_PATH) ?? '', '/') ?>/templates">Templates</a></li>
                <li><a href="<?= rtrim(parse_url($_ENV['APP_URL'] ?? getenv('APP_URL') ?: '', PHP_URL_PATH) ?? '', '/') ?>/smtp-settings">SMTP Settings</a></li>
                <li><a href="<?= rtrim(parse_url($_ENV['APP_URL'] ?? getenv('APP_URL') ?: '', PHP_URL_PATH) ?? '', '/') ?>/logout">Logout</a></li>
            </ul>
        </div>
    </nav>
    <?php endif; ?>
    
    <div class="container">
        <?= $content ?? '' ?>
    </div>
</body>
</html>
