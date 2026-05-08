<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Aggiungi Paziente</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .login-wrapper { display: flex; justify-content: center; align-items: center; height: calc(100vh - 80px); }
        .card { width: 350px; text-align: center; }
        .card input {
            width: 100%; box-sizing: border-box; display: block;
            margin: 8px 0; padding: 8px; border: 1px solid #ccc;
        }
        .card button { width: 100%; padding: 10px; margin-top: 5px; }
        label { display: block; text-align: left; font-size: 13px; margin-top: 8px; color: #2c3e50; }
        .errore { background: #fdecea; color: #c0392b; border: 1px solid #f5c6cb; border-radius: 4px; padding: 8px 10px; margin-bottom: 10px; font-size: 0.88em; }
        .successo-msg { background: #eafaf1; color: #1e8449; border: 1px solid #a9dfbf; border-radius: 4px; padding: 12px 10px; font-size: 0.9em; }
        .successo-msg a { color: #3498db; font-weight: bold; text-decoration: none; }
    </style>
</head>
<body>

<header>
    <h1>Gestione Ospedale</h1>
</header>

<nav>
    <a href="index_medico.php">Home</a>
    <a href="pazienti.php">I Miei Pazienti</a>
    <a href="visite.php">Gestione Visite</a>
    <a href="prescrizioni.php">Nuova Prescrizione</a>
    <a href="aggiungi_paziente.php">+ Aggiungi Paziente</a>
    <a href="login.php" style="float: right;">Logout</a>
</nav>

<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

$servername = 'localhost';
$db_user    = 'alessandro.betti_app';
$db_pass    = 'om%uVyN@e8yi';
$db_name    = '5DINF_Ospedale_alessandro.betti';

$conn = mysqli_connect($servername, $db_user, $db_pass, $db_name);
if (!$conn) die("Connessione fallita: " . mysqli_connect_error());

$errore   = '';
$successo = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome    = trim($_POST['nome']            ?? '');
    $cognome = trim($_POST['cognome']         ?? '');
    $nascita = trim($_POST['data_nascita']    ?? '');
    $cf      = strtoupper(trim($_POST['codice_fiscale'] ?? ''));

    if (empty($nome) || empty($cognome) || empty($nascita) || empty($cf)) {
        $errore = 'Compila tutti i campi.';
    } elseif (strlen($cf) !== 16) {
        $errore = 'Il codice fiscale deve essere di 16 caratteri.';
    } else {
        $stmt = mysqli_prepare($conn,
            "INSERT INTO PAZIENTI (nome, cognome, data_nascita, codice_fiscale) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "ssss", $nome, $cognome, $nascita, $cf);
        if (mysqli_stmt_execute($stmt)) {
            $successo = true;
        } else {
            $errore = 'Errore: ' . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    }
}

mysqli_close($conn);
?>

<div class="login-wrapper">
    <div class="card">
        <h2>Aggiungi Paziente</h2>

        <?php if ($errore): ?>
            <div class="errore"><?= htmlspecialchars($errore) ?></div>
        <?php endif; ?>

        <?php if ($successo): ?>
            <div class="successo-msg">
                Paziente aggiunto con successo!<br><br>
                <a href="pazienti.php">Vedi lista pazienti</a> &nbsp;|&nbsp;
                <a href="aggiungi_paziente.php">Aggiungi un altro</a>
            </div>
        <?php else: ?>
            <form method="POST" action="aggiungi_paziente.php">
                <label>Nome</label>
                <input type="text" name="nome" placeholder="Mario"
                    value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>" required>

                <label>Cognome</label>
                <input type="text" name="cognome" placeholder="Rossi"
                    value="<?= htmlspecialchars($_POST['cognome'] ?? '') ?>" required>

                <label>Data di Nascita</label>
                <input type="date" name="data_nascita"
                    value="<?= htmlspecialchars($_POST['data_nascita'] ?? '') ?>" required>

                <label>Codice Fiscale</label>
                <input type="text" name="codice_fiscale" placeholder="RSSMRA80A01H501U"
                    maxlength="16" style="text-transform:uppercase"
                    value="<?= htmlspecialchars($_POST['codice_fiscale'] ?? '') ?>" required>

                <button type="submit">Salva Paziente</button>
            </form>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
