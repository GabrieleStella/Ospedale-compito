<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrazione - Ospedale</title>
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
        .ruolo-gruppo {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin: 12px 0;
        }
        .ruolo-gruppo label {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.95em;
            cursor: pointer;
        }
        .ruolo-gruppo input[type="radio"] {
            width: auto;
            margin: 0;
            accent-color: #2c3e50;
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
        .successo-msg {
            background: #eafaf1;
            color: #1e8449;
            border: 1px solid #a9dfbf;
            border-radius: 4px;
            padding: 12px 10px;
            font-size: 0.9em;
        }
        .successo-msg a {
            color: #3498db;
            font-weight: bold;
            text-decoration: none;
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
    <a href="index.html">Home</a>
    <a href="login.php">Login</a>
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

$errore   = '';
$successo = false;
$sel_ruolo = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user     = $_POST['username'];
    $pass     = $_POST['password'];
    $conferma = $_POST['conferma'];
    $ruolo    = $_POST['ruolo'];
    $sel_ruolo = $ruolo;

    if (empty($user) || empty($pass) || empty($conferma)) {
        $errore = 'Compila tutti i campi.';
    } elseif (!in_array($ruolo, ['medico', 'farmacista'])) {
        $errore = 'Seleziona un ruolo: Medico o Farmacista.';
    } elseif (strlen($user) < 3) {
        $errore = 'Il nome utente deve avere almeno 3 caratteri.';
    } elseif (strlen($pass) < 8) {
        $errore = 'La password deve avere almeno 8 caratteri.';
    } elseif ($pass !== $conferma) {
        $errore = 'Le password non coincidono.';
    } else {
        $stmt = mysqli_prepare($conn, "SELECT id FROM utenti WHERE username = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, "s", $user);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) > 0) {
            $errore = 'Nome utente già in uso, scegline un altro.';
            mysqli_stmt_close($stmt);
        } else {
            mysqli_stmt_close($stmt);
            $hash = password_hash($pass, PASSWORD_BCRYPT);
            $stmt = mysqli_prepare($conn, "INSERT INTO utenti (username, password, ruolo) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "sss", $user, $hash, $ruolo);
            if (mysqli_stmt_execute($stmt)) {
                $successo = true;
            } else {
                $errore = 'Errore nella registrazione: ' . mysqli_error($conn);
            }
            mysqli_stmt_close($stmt);
        }
    }
}

mysqli_close($conn);
?>

<div class="login-wrapper">
    <div class="card">
        <h2>Registrazione</h2>

        <?php if ($errore): ?>
            <div class="errore"><?= htmlspecialchars($errore) ?></div>
        <?php endif; ?>

        <?php if ($successo): ?>
            <div class="successo-msg">
                Account creato con successo!<br><br>
                <a href="login.php">Vai al Login</a>
            </div>
        <?php else: ?>
            <form method="POST" action="registrazione.php" novalidate>
                <input type="text" name="username" placeholder="Nome utente"
                    value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                    required autocomplete="username">
                <input type="password" name="password" placeholder="Password"
                    required autocomplete="new-password">
                <input type="password" name="conferma" placeholder="Conferma password"
                    required autocomplete="new-password">

                <div class="ruolo-gruppo">
                    <label>
                        <input type="radio" name="ruolo" value="medico"
                            <?= $sel_ruolo === 'medico' ? 'checked' : '' ?>>
                        Medico
                    </label>
                    <label>
                        <input type="radio" name="ruolo" value="farmacista"
                            <?= $sel_ruolo === 'farmacista' ? 'checked' : '' ?>>
                        Farmacista
                    </label>
                </div>

                <button type="submit">Registrati</button>
            </form>
        <?php endif; ?>

        <div class="link-footer">
            Hai già un account? <a href="login.php">Torna al Login</a>
        </div>
    </div>
</div>

</body>
</html>
