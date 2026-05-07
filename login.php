<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Ospedale</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .login-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            height: calc(100vh - 80px);
        }
        .card {
            width: 300px;
            text-align: center;
        }
        .card input[type="text"],
        .card input[type="password"] {
            width: 100%;
            box-sizing: border-box;
            display: block;
            margin: 8px 0;
        }
        .card button {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
        }
        .errore {
            background: #fdecea;
            color: #c0392b;
            border: 1px solid #f5c6cb;
            border-radius: 4px;
            padding: 8px 10px;
            margin-bottom: 10px;
            font-size: 0.88em;
        }
        .link-footer {
            margin-top: 15px;
            font-size: 0.9em;
        }
        .link-footer a {
            color: #3498db;
            text-decoration: none;
        }
    </style>
</head>
<body>

<nav>
</nav>

<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

$servername = 'localhost';
$username   = 'alessandro.betti_app';
$password   = 'om%uVyN@e8yi';
$db_name    = '5DINF_Ospedale_alessandro.betti';

$conn = mysqli_connect($servername, $username, $password, $db_name);

if (!$conn) {
    die("Connessione fallita: " . mysqli_connect_error());
}

$errore = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['username'];
    $pass = $_POST['password'];

    if (empty($user) || empty($pass)) {
        $errore = 'Compila tutti i campi.';
    } else {
        $stmt = mysqli_prepare($conn, "SELECT * FROM utenti WHERE username = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, "s", $user);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $utente = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if ($utente && password_verify($pass, $utente['password'])) {
            session_start();
            session_regenerate_id(true);
            $_SESSION['utente_id']       = $utente['id'];
            $_SESSION['utente_username'] = $utente['username'];
            $_SESSION['utente_ruolo']    = $utente['ruolo'];
            mysqli_close($conn);
            if ($utente['ruolo'] === 'medico') {
                header('Location: index_medico.html');
            } else {
                header('Location: index_farmacista.html');
            }
            exit;
        } else {
            $errore = 'Nome utente o password errati.';
        }
    }
}

mysqli_close($conn);
?>

<div class="login-wrapper">
    <div class="card">
        <h2>Login</h2>

        <?php if ($errore): ?>
            <div class="errore"><?= htmlspecialchars($errore) ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php" novalidate>
            <input type="text" name="username" placeholder="Nome utente"
                value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                required autocomplete="username">
            <input type="password" name="password" placeholder="Password"
                required autocomplete="current-password">
            <button type="submit">Accedi</button>
        </form>

        <div class="link-footer">
            Non hai un account? <a href="registrazione.php">Registrati qui</a>
        </div>
    </div>
</div>

</body>
</html>
