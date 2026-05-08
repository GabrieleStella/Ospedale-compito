<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Aggiungi Farmaco</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .login-wrapper { display: flex; justify-content: center; align-items: center; height: calc(100vh - 80px); }
        .card { width: 350px; text-align: center; }
        .card input, .card select {
            width: 100%; box-sizing: border-box; display: block;
            margin: 8px 0; padding: 8px; border: 1px solid #ccc;
        }
        .card button { width: 100%; padding: 10px; margin-top: 5px; }
        .errore { background: #fdecea; color: #c0392b; border: 1px solid #f5c6cb; border-radius: 4px; padding: 8px 10px; margin-bottom: 10px; font-size: 0.88em; }
        .successo-msg { background: #eafaf1; color: #1e8449; border: 1px solid #a9dfbf; border-radius: 4px; padding: 12px 10px; font-size: 0.9em; }
        .successo-msg a { color: #3498db; font-weight: bold; text-decoration: none; }
        .link-footer { margin-top: 15px; font-size: 0.9em; }
        .link-footer a { color: #3498db; text-decoration: none; }
        label { display: block; text-align: left; font-size: 13px; margin-top: 8px; color: #2c3e50; }
    </style>
</head>
<body>

<header>
    <h1>Gestione Ospedale</h1>
</header>

<nav>
    <a href="index_farmacista.php">Home</a>
    <a href="aggiungi_farmaco.php">Aggiungi Farmaco</a>
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

// Lista farmacie per il select
$farmacie = [];
$res = mysqli_query($conn, "SELECT id_farmacia, nome FROM FARMACIA_OSPEDALIERA ORDER BY nome");
while ($row = mysqli_fetch_assoc($res)) $farmacie[] = $row;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome        = trim($_POST['nome']        ?? '');
    $quantita    = trim($_POST['quantita']    ?? '');
    $id_farmacia = trim($_POST['id_farmacia'] ?? '');

    if (empty($nome) || $quantita === '' || empty($id_farmacia)) {
        $errore = 'Compila tutti i campi.';
    } elseif (!is_numeric($quantita) || $quantita < 0) {
        $errore = 'La quantità deve essere un numero positivo.';
    } else {
        $stmt = mysqli_prepare($conn,
            "INSERT INTO FARMACI (nome, quantita_magazzino, id_farmacia) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "sii", $nome, $quantita, $id_farmacia);
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
        <h2>Aggiungi Farmaco</h2>

        <?php if ($errore): ?>
            <div class="errore"><?= htmlspecialchars($errore) ?></div>
        <?php endif; ?>

        <?php if ($successo): ?>
            <div class="successo-msg">
                Farmaco aggiunto con successo!<br><br>
                <a href="index_farmacista.php">Torna alla Home</a>
            </div>
        <?php else: ?>
            <form method="POST" action="aggiungi_farmaco.php">
                <label>Nome Farmaco</label>
                <input type="text" name="nome" placeholder="es. Tachipirina 500mg"
                    value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>" required>

                <label>Quantità iniziale in magazzino</label>
                <input type="number" name="quantita" min="0"
                    value="<?= htmlspecialchars($_POST['quantita'] ?? '') ?>" required>

                <label>Farmacia</label>
                <select name="id_farmacia" required>
                    <option value="">-- Seleziona Farmacia --</option>
                    <?php foreach ($farmacie as $f): ?>
                        <option value="<?= $f['id_farmacia'] ?>"
                            <?= (($_POST['id_farmacia'] ?? '') == $f['id_farmacia']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($f['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <button type="submit">Aggiungi Farmaco</button>
            </form>
        <?php endif; ?>

        <?php if (!$successo): ?>
        <div class="link-footer">
            <a href="index_farmacista.php">← Torna alla Home</a>
        </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
