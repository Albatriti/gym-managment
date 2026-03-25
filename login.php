<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: ' . ($_SESSION['role'] === 'admin' ? 'views/admin/dashboard.php' : ($_SESSION['role'] === 'staff' ? 'views/staff/dashboard.php' : 'views/member/dashboard.php')));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'data/Database.php';
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->execute([':email' => $_POST['email']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($_POST['password'], $user['password'])) {
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['role']      = $user['role'];
        $_SESSION['firstName'] = $user['first_name'];
        $_SESSION['lastName']  = $user['last_name'];
        header('Location: ' . ($user['role'] === 'admin' ? 'views/admin/dashboard.php' : ($user['role'] === 'staff' ? 'views/staff/dashboard.php' : 'views/member/dashboard.php')));
        exit;
    } else {
        $error = "Email ose fjalëkalimi është i gabuar!";
    }
}
?>
<!DOCTYPE html>
<html lang="sq">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login — GymFlow</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #E8FF47;
            --dark: #0a0a0a;
            --dark2: #111111;
            --dark3: #1a1a1a;
            --border: #2a2a2a;
            --text: #f0f0f0;
            --text-muted: #888;
            --danger: #ff4757;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--dark);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-wrapper {
            display: flex;
            width: 100%;
            max-width: 900px;
            min-height: 520px;
            border: 1px solid var(--border);
            border-radius: 20px;
            overflow: hidden;
            margin: 20px;
        }

        .login-left {
            flex: 1;
            background: var(--primary);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px;
        }

        .login-left h1 {
            font-family: 'Bebas Neue', cursive;
            font-size: 3.5rem;
            color: var(--dark);
            letter-spacing: 4px;
            line-height: 1;
        }

        .login-left p {
            font-size: 0.85rem;
            color: var(--dark);
            margin-top: 8px;
            opacity: 0.7;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .login-left .logo-icon {
            font-size: 3rem;
            margin-bottom: 12px;
        }

        .login-right {
            flex: 1;
            background: var(--dark2);
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 48px;
        }

        .login-right h2 {
            font-family: 'Bebas Neue', cursive;
            font-size: 1.8rem;
            letter-spacing: 2px;
            margin-bottom: 8px;
        }

        .login-right p {
            font-size: 0.82rem;
            color: var(--text-muted);
            margin-bottom: 32px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-label {
            display: block;
            font-size: 0.72rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--text-muted);
            margin-bottom: 8px;
        }

        .form-control {
            width: 100%;
            background: var(--dark3);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 12px 16px;
            color: var(--text);
            font-size: 0.9rem;
            font-family: 'DM Sans', sans-serif;
            outline: none;
            transition: border-color 0.2s;
        }

        .form-control:focus {
            border-color: var(--primary);
        }

        .btn-login {
            width: 100%;
            background: var(--primary);
            color: var(--dark);
            border: none;
            padding: 13px;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 700;
            cursor: pointer;
            font-family: 'DM Sans', sans-serif;
            letter-spacing: 1px;
            transition: all 0.2s;
            margin-top: 8px;
        }

        .btn-login:hover {
            background: #c8df20;
        }

        .error {
            background: rgba(255, 71, 87, 0.1);
            border: 1px solid rgba(255, 71, 87, 0.3);
            color: var(--danger);
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 0.82rem;
            margin-bottom: 18px;
        }

        .register-link {
            text-align: center;
            margin-top: 20px;
            font-size: 0.82rem;
            color: var(--text-muted);
        }

        .register-link a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s;
        }

        .register-link a:hover {
            color: var(--dark);
            background: var(--primary);
            padding: 2px 8px;
            border-radius: 4px;
        }
    </style>
</head>

<body>
    <div class="login-wrapper">
        <div class="login-left">
            <div class="logo-icon">🏟️</div>
            <h1>GYMFLOW</h1>
            <p>Management System</p>
        </div>
        <div class="login-right">
            <h2>Mirë se vjen!</h2>
            <p>Hyr në llogarinë tënde për të vazhduar</p>

            <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input class="form-control" type="email" name="email" placeholder="emri@email.com" required />
                </div>
                <div class="form-group">
                    <label class="form-label">Fjalëkalimi</label>
                    <input class="form-control" type="password" name="password" placeholder="••••••••" required />
                </div>
                <button class="btn-login" type="submit">HYR</button>
            </form>

            <div class="register-link">
                Nuk ke llogari? <a href="register.php">Regjistrohu</a>
            </div>
        </div>
    </div>
</body>

</html>