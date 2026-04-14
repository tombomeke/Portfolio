<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Under Maintenance — tombomeke.com</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #0d1117;
            color: #c9d1d9;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 2rem;
        }
        .maintenance-box {
            max-width: 480px;
            text-align: center;
        }
        .maintenance-icon {
            font-size: 4rem;
            margin-bottom: 1.5rem;
            opacity: .75;
        }
        h1 {
            font-size: 1.75rem;
            font-weight: 700;
            color: #e6edf3;
            margin-bottom: .75rem;
        }
        p {
            font-size: 1rem;
            line-height: 1.6;
            color: #8b949e;
        }
        .back-soon {
            margin-top: 1.5rem;
            display: inline-block;
            padding: .5rem 1.25rem;
            border: 1px solid #30363d;
            border-radius: 6px;
            color: #58a6ff;
            text-decoration: none;
            font-size: .9rem;
        }
    </style>
</head>
<body>
    <div class="maintenance-box">
        <div class="maintenance-icon">🔧</div>
        <h1>Under Maintenance</h1>
        <p>
            <?php
            $msg = null;
            try {
                require_once __DIR__ . '/../app/Models/SiteSettingModel.php';
                $msg = SiteSettingModel::get('maintenance_message');
            } catch (\Throwable $e) {}
            echo htmlspecialchars($msg ?: 'We\'re making some improvements. Back shortly!');
            ?>
        </p>
        <a href="mailto:tom1dekoning@gmail.com" class="back-soon">Contact via e-mail</a>
    </div>
</body>
</html>
