<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Prescrizioni</title>
    <link rel="stylesheet" href="style.css">
    <style>
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { text-align: left; padding: 10px; border-bottom: 1px solid #ddd; }
        .btn { background: #2c3e50; color: white; padding: 6px 12px; text-decoration: none; border-radius: 4px; font-size: 14px; border: none; cursor: pointer; }
        .btn:hover { background: #34495e; }
        .nessun-dato { color: #999; font-style: italic; }
        .errore { background: #fdecea; color: #c0392b; border: 1px solid #f5c6cb; border-radius: 4px; padding: 8px 10px; margin-bottom: 10px; font-size: 0.88em; }
        .successo-msg { background: #eafaf1; color: #1e8449; border: 1px solid #a9dfbf; border-radius: 4px; padding: 8px 10px; margin-bottom: 10px; font-size: 0.88em; }
        .form-nuova { display: none; margin-top: 20px; border-top: 1px solid #ddd; padding-top: 15px; }
        .form-nuova input, .form-nuova select {
            display: block; width: 100%; box-sizing: border-box;
            margin: 6px 0; padding: 8px; border: 1px solid #ccc;
        }
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

$username_loggato = $_SESSION['utente_username'] ?? null;

$servername = 'localhost';
$db_user    = 'alessandro.betti_app';
$db_pass    = 'om%uVyN@e8yi';
$db_name    = '5DINF_Ospedale_alessandro.betti';

$conn = mysqli_connect($servername, $db_user, $db_pass, $db_name);
if (!$conn) die("Connessione fallita: " . mysqli_connect_error());

// Recupero id_medico
$id_medico = null;
if ($username_loggato) {
    $stmt = mysqli_prepare($conn, "SELECT id_medico FROM MEDICI WHERE nome = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "s", $username_loggato);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);
    if ($row) $id_medico = $row['id_medico'];
}

$errore   = '';
$successo = '';

// ------------------------------------------------
// Aggiunta nuova prescrizione
// ------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nuova_prescrizione'])) {
    $id_visita = $_POST['id_visita'] ?? '';
    $posologia = trim($_POST['posologia'] ?? '');
    $quantita  = trim($_POST['quantita']  ?? '');

    if (empty($id_visita) || empty($posologia) || empty($quantita)) {
        $errore = 'Compila tutti i campi.';
    } elseif (!is_numeric($quantita) || $quantita <= 0) {
        $errore = 'La quantità deve essere un numero positivo.';
    } else {
        $stmt = mysqli_prepare($conn,
            "INSERT INTO PRESCRIZIONI (posologia, quantita, id_visita) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "sii", $posologia, $quantita, $id_visita);
        if (mysqli_stmt_execute($stmt)) {
            $successo = 'Prescrizione aggiunta con successo.';
        } else {
            $errore = 'Errore: ' . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    }
}

// ------------------------------------------------
// Lista visite del medico (per il select)
// ------------------------------------------------
$visite = [];
if ($id_medico) {
    $stmt = mysqli_prepare($conn,
        "SELECT v.id_visita, v.data_ora, p.nome AS pnome, p.cognome AS pcognome
         FROM VISITE v
         JOIN PAZIENTI p ON v.id_paziente = p.id_paziente
         WHERE v.id_medico = ?
         ORDER BY v.data_ora DESC");
    mysqli_stmt_bind_param($stmt, "i", $id_medico);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($res)) $visite[] = $row;
    mysqli_stmt_close($stmt);
}

// ------------------------------------------------
// Lista prescrizioni del medico loggato
// ------------------------------------------------
$prescrizioni = [];
if ($id_medico) {
    $stmt = mysqli_prepare($conn,
        "SELECT pr.id_prescrizione, pr.posologia, pr.quantita,
                p.nome AS pnome, p.cognome AS pcognome,
                v.data_ora
         FROM PRESCRIZIONI pr
         JOIN VISITE v   ON pr.id_visita  = v.id_visita
         JOIN PAZIENTI p ON v.id_paziente = p.id_paziente
         WHERE v.id_medico = ?
         ORDER BY v.data_ora DESC");
    mysqli_stmt_bind_param($stmt, "i", $id_medico);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($res)) $prescrizioni[] = $row;
    mysqli_stmt_close($stmt);
}

mysqli_close($conn);
?>

<div class="container">
    <div class="card">
        <h2>Prescrizioni</h2>

        <?php if ($errore): ?>
            <div class="errore"><?= htmlspecialchars($errore) ?></div>
        <?php endif; ?>
        <?php if ($successo): ?>
            <div class="successo-msg"><?= htmlspecialchars($successo) ?></div>
        <?php endif; ?>

        <button class="btn" onclick="toggleForm()">+ Nuova Prescrizione</button>

        <!-- Form nuova prescrizione -->
        <div class="form-nuova" id="form-nuova">
            <h3>Nuova Prescrizione</h3>
            <form method="POST" action="prescrizioni.php">
                <label>Visita</label>
                <select name="id_visita" required>
                    <option value="">-- Seleziona Visita --</option>
                    <?php foreach ($visite as $v): ?>
                        <option value="<?= $v['id_visita'] ?>"
                            <?= (($_POST['id_visita'] ?? '') == $v['id_visita']) ? 'selected' : '' ?>>
                            <?= date('d/m/Y H:i', strtotime($v['data_ora'])) ?>
                            — <?= htmlspecialchars($v['pcognome'] . ' ' . $v['pnome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <label>Posologia (farmaco e dosaggio)</label>
                <input type="text" name="posologia" placeholder="es. Tachipirina 500mg 3 volte al giorno"
                    value="<?= htmlspecialchars($_POST['posologia'] ?? '') ?>" required>
                <label>Quantità</label>
                <input type="number" name="quantita" placeholder="es. 20" min="1"
                    value="<?= htmlspecialchars($_POST['quantita'] ?? '') ?>" required>
                <button type="submit" name="nuova_prescrizione" class="btn" style="margin-top:10px">Salva Prescrizione</button>
            </form>
        </div>

        <!-- Tabella prescrizioni -->
        <table>
            <tr>
                <th>Data Visita</th>
                <th>Paziente</th>
                <th>Posologia</th>
                <th>Quantità</th>
            </tr>
            <?php if (empty($prescrizioni)): ?>
            <tr><td colspan="4" class="nessun-dato">Nessuna prescrizione registrata.</td></tr>
            <?php else: ?>
                <?php foreach ($prescrizioni as $pr): ?>
                <tr>
                    <td><?= date('d/m/Y', strtotime($pr['data_ora'])) ?></td>
                    <td><?= htmlspecialchars($pr['pcognome'] . ' ' . $pr['pnome']) ?></td>
                    <td><?= htmlspecialchars($pr['posologia']) ?></td>
                    <td><?= htmlspecialchars($pr['quantita']) ?></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </table>
    </div>
</div>

<script>
function toggleForm() {
    var f = document.getElementById('form-nuova');
    f.style.display = f.style.display === 'block' ? 'none' : 'block';
}
<?php if ($errore): ?>
document.getElementById('form-nuova').style.display = 'block';
<?php endif; ?>
</script>

</body>
</html>
