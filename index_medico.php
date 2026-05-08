<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Ospedale - Dashboard Medico</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f4f4f4; }
        header { background: #2c3e50; color: white; padding: 15px; }
        nav { background: #34495e; padding: 10px; }
        nav a { color: white; margin-right: 15px; text-decoration: none; }
        .container { padding: 20px; }
        .card { background: white; padding: 15px; margin-bottom: 15px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .btn { background: #2c3e50; color: white; padding: 8px 15px; text-decoration: none; border-radius: 4px; font-size: 14px; margin-right: 5px; }
        .btn:hover { background: #34495e; }
        .reparto-tag { background: #3498db; color: white; padding: 3px 8px; border-radius: 12px; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { text-align: left; padding: 10px; border-bottom: 1px solid #ddd; }
        .nessun-dato { color: #999; font-style: italic; }
    </style>
</head>
<body>

<header>
    <h1>Gestione Ospedale - Area Medica</h1>
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

session_start();

// Recupero username dalla sessione (salvato al login)
$username_loggato = $_SESSION['utente_username'] ?? null;

$servername = 'localhost';
$db_user    = 'alessandro.betti_app';
$db_pass    = 'om%uVyN@e8yi';
$db_name    = '5DINF_Ospedale_alessandro.betti';

$conn = mysqli_connect($servername, $db_user, $db_pass, $db_name);
if (!$conn) {
    die("Connessione fallita: " . mysqli_connect_error());
}

// ------------------------------------------------
// 1. Dati del medico loggato (nome, reparto)
// ------------------------------------------------
$nome_medico    = 'Dottore';
$reparto_medico = 'N/D';
$id_medico      = null;

if ($username_loggato) {
    $stmt = mysqli_prepare($conn,
        "SELECT m.id_medico, m.nome, r.nome_reparto
         FROM MEDICI m
         JOIN REPARTI r ON m.id_reparto = r.id_reparto
         WHERE m.nome = ? LIMIT 1"
    );
    mysqli_stmt_bind_param($stmt, "s", $username_loggato);
    mysqli_stmt_execute($stmt);
    $res    = mysqli_stmt_get_result($stmt);
    $medico = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);

    if ($medico) {
        $nome_medico    = $medico['nome'];
        $reparto_medico = $medico['nome_reparto'];
        $id_medico      = $medico['id_medico'];
    }
}

// ------------------------------------------------
// 2. Visite di oggi del medico loggato
// ------------------------------------------------
$visite_oggi = [];

if ($id_medico) {
    $stmt = mysqli_prepare($conn,
        "SELECT v.id_visita, v.data_ora, p.nome AS pnome, p.cognome AS pcognome
         FROM VISITE v
         JOIN PAZIENTI p ON v.id_paziente = p.id_paziente
         WHERE v.id_medico = ?
           AND DATE(v.data_ora) = CURDATE()
         ORDER BY v.data_ora ASC"
    );
    mysqli_stmt_bind_param($stmt, "i", $id_medico);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($res)) {
        $visite_oggi[] = $row;
    }
    mysqli_stmt_close($stmt);
}

// ------------------------------------------------
// 3. Ultime 5 prescrizioni del medico loggato
// ------------------------------------------------
$ultime_prescrizioni = [];

if ($id_medico) {
    $stmt = mysqli_prepare($conn,
        "SELECT p.nome AS pnome, p.cognome AS pcognome,
                pr.posologia, pr.quantita,
                v.data_ora
         FROM PRESCRIZIONI pr
         JOIN VISITE v   ON pr.id_visita  = v.id_visita
         JOIN PAZIENTI p ON v.id_paziente = p.id_paziente
         WHERE v.id_medico = ?
         ORDER BY v.data_ora DESC
         LIMIT 5"
    );
    mysqli_stmt_bind_param($stmt, "i", $id_medico);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($res)) {
        $ultime_prescrizioni[] = $row;
    }
    mysqli_stmt_close($stmt);
}

mysqli_close($conn);
?>

<div class="container">

    <!-- Benvenuto + reparto -->
    <div class="card">
        <h2>
            Benvenuto <?= htmlspecialchars($nome_medico) ?>
            <span class="reparto-tag">Reparto: <?= htmlspecialchars($reparto_medico) ?></span>
        </h2>
        <div style="margin-top: 15px;">
            <a href="visite.php" class="btn">+ Crea Nuova Visita</a>
            <a href="prescrizioni.php" class="btn">+ Fai Prescrizione</a>
        </div>
    </div>

    <!-- Visite di oggi -->
    <div class="card">
        <h3>Visite Programmate Oggi</h3>
        <table>
            <tr>
                <th>Paziente</th>
                <th>Orario</th>
                <th>Azioni</th>
            </tr>
            <?php if (empty($visite_oggi)): ?>
            <tr>
                <td colspan="3" class="nessun-dato">Nessuna visita programmata per oggi.</td>
            </tr>
            <?php else: ?>
                <?php foreach ($visite_oggi as $v): ?>
                <tr>
                    <td><?= htmlspecialchars($v['pnome'] . ' ' . $v['pcognome']) ?></td>
                    <td><?= date('H:i', strtotime($v['data_ora'])) ?></td>
                    <td><a href="visita.php?id=<?= $v['id_visita'] ?>" class="btn">Vedi Cartella</a></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </table>
    </div>

    <!-- Ultime prescrizioni -->
    <div class="card">
        <h3>Ultime Prescrizioni Effettuate</h3>
        <table>
            <tr>
                <th>Paziente</th>
                <th>Posologia</th>
                <th>Quantità</th>
                <th>Data Visita</th>
            </tr>
            <?php if (empty($ultime_prescrizioni)): ?>
            <tr>
                <td colspan="4" class="nessun-dato">Nessuna prescrizione trovata.</td>
            </tr>
            <?php else: ?>
                <?php foreach ($ultime_prescrizioni as $pr): ?>
                <tr>
                    <td><?= htmlspecialchars($pr['pnome'] . ' ' . $pr['pcognome']) ?></td>
                    <td><?= htmlspecialchars($pr['posologia']) ?></td>
                    <td><?= htmlspecialchars($pr['quantita']) ?></td>
                    <td><?= date('d/m/Y', strtotime($pr['data_ora'])) ?></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </table>
    </div>

</div>

</body>
</html>
