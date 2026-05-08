<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Pazienti</title>
    <link rel="stylesheet" href="style.css">
    <style>
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { text-align: left; padding: 10px; border-bottom: 1px solid #ddd; }
        .btn { background: #2c3e50; color: white; padding: 6px 12px; text-decoration: none; border-radius: 4px; font-size: 14px; border: none; cursor: pointer; }
        .btn:hover { background: #34495e; }
        .nessun-dato { color: #999; font-style: italic; }
        .errore { background: #fdecea; color: #c0392b; border: 1px solid #f5c6cb; border-radius: 4px; padding: 8px 10px; margin-bottom: 10px; font-size: 0.88em; }
        .successo-msg { background: #eafaf1; color: #1e8449; border: 1px solid #a9dfbf; border-radius: 4px; padding: 8px 10px; margin-bottom: 10px; font-size: 0.88em; }
        .form-aggiungi { display: none; margin-top: 20px; border-top: 1px solid #ddd; padding-top: 15px; }
        .form-aggiungi input { display: block; width: 100%; box-sizing: border-box; margin: 6px 0; padding: 8px; border: 1px solid #ccc; }
        .search-bar { display: flex; gap: 10px; margin-top: 10px; }
        .search-bar input { flex: 1; padding: 8px; border: 1px solid #ccc; }
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
    <a href="login.php" style="float:right">Logout</a>
</nav>

<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

session_start();

$servername = 'localhost';
$db_user    = 'alessandro.betti_app';
$db_pass    = 'om%uVyN@e8yi';
$db_name    = '5DINF_Ospedale_alessandro.betti';

$conn = mysqli_connect($servername, $db_user, $db_pass, $db_name);
if (!$conn) die("Connessione fallita: " . mysqli_connect_error());

$errore      = '';
$successo    = '';

// ------------------------------------------------
// Aggiunta nuovo paziente
// ------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aggiungi'])) {
    $nome   = trim($_POST['nome']   ?? '');
    $cognome = trim($_POST['cognome'] ?? '');
    $nascita = trim($_POST['data_nascita'] ?? '');
    $cf      = strtoupper(trim($_POST['codice_fiscale'] ?? ''));

    if (empty($nome) || empty($cognome) || empty($nascita) || empty($cf)) {
        $errore = 'Compila tutti i campi.';
    } elseif (strlen($cf) !== 16) {
        $errore = 'Il codice fiscale deve essere di 16 caratteri.';
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO PAZIENTI (nome, cognome, data_nascita, codice_fiscale) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "ssss", $nome, $cognome, $nascita, $cf);
        if (mysqli_stmt_execute($stmt)) {
            $successo = 'Paziente aggiunto con successo.';
        } else {
            $errore = 'Errore: ' . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    }
}

// ------------------------------------------------
// Ricerca e lista pazienti
// ------------------------------------------------
$cerca = trim($_GET['cerca'] ?? '');
if ($cerca !== '') {
    $like = '%' . $cerca . '%';
    $stmt = mysqli_prepare($conn,
        "SELECT * FROM PAZIENTI WHERE nome LIKE ? OR cognome LIKE ? OR codice_fiscale LIKE ? ORDER BY cognome, nome");
    mysqli_stmt_bind_param($stmt, "sss", $like, $like, $like);
} else {
    $stmt = mysqli_prepare($conn, "SELECT * FROM PAZIENTI ORDER BY cognome, nome");
}
mysqli_stmt_execute($stmt);
$res      = mysqli_stmt_get_result($stmt);
$pazienti = [];
while ($row = mysqli_fetch_assoc($res)) $pazienti[] = $row;
mysqli_stmt_close($stmt);

mysqli_close($conn);
?>

<div class="container">
    <div class="card">
        <h2>Pazienti</h2>

        <?php if ($errore): ?>
            <div class="errore"><?= htmlspecialchars($errore) ?></div>
        <?php endif; ?>
        <?php if ($successo): ?>
            <div class="successo-msg"><?= htmlspecialchars($successo) ?></div>
        <?php endif; ?>

        <!-- Ricerca -->
        <form method="GET" action="pazienti.php">
            <div class="search-bar">
                <input type="text" name="cerca" placeholder="Cerca per nome, cognome o CF..."
                    value="<?= htmlspecialchars($cerca) ?>">
                <button type="submit" class="btn">Cerca</button>
                <?php if ($cerca): ?>
                    <a href="pazienti.php" class="btn">✕ Reset</a>
                <?php endif; ?>
            </div>
        </form>

        <button class="btn" style="margin-top:15px" onclick="toggleForm()">+ Aggiungi Paziente</button>

        <!-- Form aggiunta -->
        <div class="form-aggiungi" id="form-aggiungi">
            <h3>Nuovo Paziente</h3>
            <form method="POST" action="pazienti.php">
                <input type="text" name="nome" placeholder="Nome" required>
                <input type="text" name="cognome" placeholder="Cognome" required>
                <input type="date" name="data_nascita" required>
                <input type="text" name="codice_fiscale" placeholder="Codice Fiscale" maxlength="16" required>
                <button type="submit" name="aggiungi" class="btn" style="margin-top:10px">Salva Paziente</button>
            </form>
        </div>

        <!-- Tabella pazienti -->
        <table>
            <tr>
                <th>Cognome</th>
                <th>Nome</th>
                <th>Data di Nascita</th>
                <th>Codice Fiscale</th>
            </tr>
            <?php if (empty($pazienti)): ?>
            <tr><td colspan="4" class="nessun-dato">Nessun paziente trovato.</td></tr>
            <?php else: ?>
                <?php foreach ($pazienti as $p): ?>
                <tr>
                    <td><?= htmlspecialchars($p['cognome']) ?></td>
                    <td><?= htmlspecialchars($p['nome']) ?></td>
                    <td><?= date('d/m/Y', strtotime($p['data_nascita'])) ?></td>
                    <td><?= htmlspecialchars($p['codice_fiscale']) ?></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </table>
    </div>
</div>

<script>
function toggleForm() {
    var f = document.getElementById('form-aggiungi');
    f.style.display = f.style.display === 'block' ? 'none' : 'block';
}
<?php if ($errore): ?>
document.getElementById('form-aggiungi').style.display = 'block';
<?php endif; ?>
</script>

</body>
</html>
