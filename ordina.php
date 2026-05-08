<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Gestione Ordine Farmaco</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .card { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); width: 350px; }
        h2 { color: #2c3e50; text-align: center; }
        label { font-size: 14px; color: #2c3e50; }
        input { width: 100%; padding: 10px; margin: 6px 0 14px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        input[readonly] { background: #f0f0f0; color: #555; }
        button { width: 100%; padding: 12px; background: #27ae60; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
        button:hover { background: #2ecc71; }
        .back-link { display: block; text-align: center; margin-top: 15px; color: #3498db; text-decoration: none; }
        .errore { color: #c0392b; background: #fdecea; border: 1px solid #f5c6cb; padding: 8px; border-radius: 4px; margin-bottom: 10px; font-size: 0.88em; }
    </style>
</head>
<body>

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

$errore = '';

// ------------------------------------------------
// Salvataggio ordine
// ------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome_farmaco = $_POST['nome_farmaco'] ?? '';
    $quantita     = $_POST['quantita']     ?? '';

    if (empty($nome_farmaco) || empty($quantita) || $quantita <= 0) {
        $errore = 'Compila tutti i campi correttamente.';
    } else {
        $stmt = mysqli_prepare($conn,
            "UPDATE FARMACI SET quantita_magazzino = quantita_magazzino + ? WHERE nome = ?");
        mysqli_stmt_bind_param($stmt, "is", $quantita, $nome_farmaco);
        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            mysqli_close($conn);
            header("Location: index_farmacista.php?successo=1");
            exit();
        } else {
            $errore = "Errore durante l'ordine: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    }
}

// Nome farmaco passato via GET dal bottone Ordina
$nome_farmaco_get = $_POST['nome_farmaco'] ?? $_GET['farmaco'] ?? '';

mysqli_close($conn);
?>

<div class="card">
    <h2>Nuovo Ordine</h2>

    <?php if ($errore): ?>
        <div class="errore"><?= htmlspecialchars($errore) ?></div>
    <?php endif; ?>

    <form method="POST" action="ordina.php">
        <label>Nome Farmaco:</label>
        <!-- Campo precompilato e readonly: arriva dal bottone Ordina -->
        <input type="text" name="nome_farmaco"
            value="<?= htmlspecialchars($nome_farmaco_get) ?>"
            readonly>

        <label>Quantità da aggiungere:</label>
        <input type="number" name="quantita" min="1"
            value="<?= htmlspecialchars($_POST['quantita'] ?? '') ?>"
            required>

        <button type="submit">Conferma Ordine</button>
    </form>

    <a href="index_farmacista.php" class="back-link">Torna alla Home</a>
</div>

</body>
</html>
