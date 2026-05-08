<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Visite</title>
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
        .form-nuova input, .form-nuova select, .form-nuova textarea {
            display: block; width: 100%; box-sizing: border-box;
            margin: 6px 0; padding: 8px; border: 1px solid #ccc;
        }
        .form-nuova textarea { resize: vertical; height: 80px; }
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

// Recupero id_medico dalla sessione
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
// Aggiunta nuova visita
// ------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nuova_visita'])) {
    $id_paziente      = $_POST['id_paziente']      ?? '';
    $data_ora         = $_POST['data_ora']          ?? '';
    $parametri_vitali = trim($_POST['parametri_vitali'] ?? '');
    $note             = trim($_POST['note']         ?? '');

    if (empty($id_paziente) || empty($data_ora)) {
        $errore = 'Seleziona paziente e data/ora.';
    } elseif (!$id_medico) {
        $errore = 'Medico non riconosciuto. Effettua di nuovo il login.';
    } else {
        $stmt = mysqli_prepare($conn,
            "INSERT INTO VISITE (data_ora, parametri_vitali, note, id_medico, id_paziente) VALUES (?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "sssii", $data_ora, $parametri_vitali, $note, $id_medico, $id_paziente);
        if (mysqli_stmt_execute($stmt)) {
            $successo = 'Visita aggiunta con successo.';
        } else {
            $errore = 'Errore: ' . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    }
}

// ------------------------------------------------
// Lista pazienti per il select
// ------------------------------------------------
$pazienti = [];
$res = mysqli_query($conn, "SELECT id_paziente, nome, cognome FROM PAZIENTI ORDER BY cognome, nome");
while ($row = mysqli_fetch_assoc($res)) $pazienti[] = $row;

// ------------------------------------------------
// Lista visite del medico loggato
// ------------------------------------------------
$visite = [];
if ($id_medico) {
    $stmt = mysqli_prepare($conn,
        "SELECT v.id_visita, v.data_ora, v.parametri_vitali, v.note,
                p.nome AS pnome, p.cognome AS pcognome
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

mysqli_close($conn);
?>

<div class="container">
    <div class="card">
        <h2>Visite</h2>

        <?php if ($errore): ?>
            <div class="errore"><?= htmlspecialchars($errore) ?></div>
        <?php endif; ?>
        <?php if ($successo): ?>
            <div class="successo-msg"><?= htmlspecialchars($successo) ?></div>
        <?php endif; ?>

        <button class="btn" onclick="toggleForm()">+ Nuova Visita</button>

        <!-- Form nuova visita -->
        <div class="form-nuova" id="form-nuova">
            <h3>Nuova Visita</h3>
            <form method="POST" action="visite.php">
                <label>Paziente</label>
                <select name="id_paziente" required>
                    <option value="">-- Seleziona Paziente --</option>
                    <?php foreach ($pazienti as $p): ?>
                        <option value="<?= $p['id_paziente'] ?>"
                            <?= (($_POST['id_paziente'] ?? '') == $p['id_paziente']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p['cognome'] . ' ' . $p['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <label>Data e Ora</label>
                <input type="datetime-local" name="data_ora" required
                    value="<?= htmlspecialchars($_POST['data_ora'] ?? '') ?>">
                <label>Parametri Vitali</label>
                <input type="text" name="parametri_vitali" placeholder="es. PA 120/80, FC 75..."
                    value="<?= htmlspecialchars($_POST['parametri_vitali'] ?? '') ?>">
                <label>Note</label>
                <textarea name="note" placeholder="Note cliniche..."><?= htmlspecialchars($_POST['note'] ?? '') ?></textarea>
                <button type="submit" name="nuova_visita" class="btn" style="margin-top:10px">Salva Visita</button>
            </form>
        </div>

        <!-- Tabella visite -->
        <table>
            <tr>
                <th>Data</th>
                <th>Paziente</th>
                <th>Parametri Vitali</th>
                <th>Note</th>
            </tr>
            <?php if (empty($visite)): ?>
            <tr><td colspan="4" class="nessun-dato">Nessuna visita registrata.</td></tr>
            <?php else: ?>
                <?php foreach ($visite as $v): ?>
                <tr>
                    <td><?= date('d/m/Y H:i', strtotime($v['data_ora'])) ?></td>
                    <td><?= htmlspecialchars($v['pcognome'] . ' ' . $v['pnome']) ?></td>
                    <td><?= htmlspecialchars($v['parametri_vitali'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($v['note'] ?? '—') ?></td>
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
