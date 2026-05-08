<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Cartella Clinica</title>
    <link rel="stylesheet" href="style.css">
    <style>
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { text-align: left; padding: 10px; border-bottom: 1px solid #ddd; }
        th { background: #f8f9fa; }
        .btn { background: #2c3e50; color: white; padding: 6px 12px; text-decoration: none; border-radius: 4px; font-size: 14px; }
        .btn:hover { background: #34495e; }
        .nessun-dato { color: #999; font-style: italic; }
        .errore { background: #fdecea; color: #c0392b; border: 1px solid #f5c6cb; border-radius: 4px; padding: 8px 10px; margin-bottom: 10px; font-size: 0.88em; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 10px; }
        .info-item label { font-weight: bold; font-size: 0.85em; color: #555; display: block; margin-bottom: 2px; }
        .info-item span { font-size: 1em; }
        .badge-reparto { background: #3498db; color: white; padding: 3px 8px; border-radius: 12px; font-size: 12px; }
    </style>
</head>
<body>

<header>
    <h1>Gestione Ospedale - Cartella Clinica</h1>
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

$id_visita = intval($_GET['id'] ?? 0);

if (!$id_visita) {
    echo '<div class="container"><div class="card"><div class="errore">ID visita non valido.</div><a href="index_medico.php" class="btn">Torna alla Home</a></div></div>';
    mysqli_close($conn);
    exit;
}

// ------------------------------------------------
// Dati visita + paziente + medico + reparto
// ------------------------------------------------
$stmt = mysqli_prepare($conn,
    "SELECT v.id_visita, v.data_ora, v.parametri_vitali, v.note,
            p.id_paziente, p.nome AS pnome, p.cognome AS pcognome,
            p.data_nascita, p.codice_fiscale,
            m.nome AS mnome,
            r.nome_reparto
     FROM VISITE v
     JOIN PAZIENTI p ON v.id_paziente = p.id_paziente
     JOIN MEDICI m   ON v.id_medico   = m.id_medico
     JOIN REPARTI r  ON m.id_reparto  = r.id_reparto
     WHERE v.id_visita = ? LIMIT 1"
);
mysqli_stmt_bind_param($stmt, "i", $id_visita);
mysqli_stmt_execute($stmt);
$res   = mysqli_stmt_get_result($stmt);
$visita = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

if (!$visita) {
    echo '<div class="container"><div class="card"><div class="errore">Visita non trovata.</div><a href="index_medico.php" class="btn">Torna alla Home</a></div></div>';
    mysqli_close($conn);
    exit;
}

// ------------------------------------------------
// Prescrizioni associate a questa visita
// ------------------------------------------------
$prescrizioni = [];
$stmt = mysqli_prepare($conn,
    "SELECT id_prescrizione, posologia, quantita
     FROM PRESCRIZIONI
     WHERE id_visita = ?
     ORDER BY id_prescrizione ASC"
);
mysqli_stmt_bind_param($stmt, "i", $id_visita);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($res)) $prescrizioni[] = $row;
mysqli_stmt_close($stmt);

mysqli_close($conn);
?>

<div class="container">

    <!-- Dati Paziente -->
    <div class="card">
        <h2>Cartella Clinica — <?= htmlspecialchars($visita['pcognome'] . ' ' . $visita['pnome']) ?></h2>
        <div class="info-grid">
            <div class="info-item">
                <label>Nome Completo</label>
                <span><?= htmlspecialchars($visita['pnome'] . ' ' . $visita['pcognome']) ?></span>
            </div>
            <div class="info-item">
                <label>Data di Nascita</label>
                <span><?= date('d/m/Y', strtotime($visita['data_nascita'])) ?></span>
            </div>
            <div class="info-item">
                <label>Codice Fiscale</label>
                <span><?= htmlspecialchars($visita['codice_fiscale']) ?></span>
            </div>
            <div class="info-item">
                <label>Medico</label>
                <span>
                    <?= htmlspecialchars($visita['mnome']) ?>
                    <span class="badge-reparto"><?= htmlspecialchars($visita['nome_reparto']) ?></span>
                </span>
            </div>
        </div>
    </div>

    <!-- Dettagli Visita -->
    <div class="card">
        <h3>Dettagli Visita — <?= date('d/m/Y H:i', strtotime($visita['data_ora'])) ?></h3>
        <div class="info-grid">
            <div class="info-item">
                <label>Parametri Vitali</label>
                <span><?= $visita['parametri_vitali'] ? htmlspecialchars($visita['parametri_vitali']) : '—' ?></span>
            </div>
            <div class="info-item">
                <label>Note Cliniche</label>
                <span><?= $visita['note'] ? htmlspecialchars($visita['note']) : '—' ?></span>
            </div>
        </div>
    </div>

    <!-- Prescrizioni della visita -->
    <div class="card">
        <h3>Prescrizioni</h3>
        <a href="prescrizioni.php" class="btn" style="margin-bottom:10px; display:inline-block;">+ Aggiungi Prescrizione</a>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Posologia / Farmaco</th>
                    <th>Quantità</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($prescrizioni)): ?>
                <tr><td colspan="3" class="nessun-dato">Nessuna prescrizione per questa visita.</td></tr>
                <?php else: ?>
                    <?php foreach ($prescrizioni as $i => $pr): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= htmlspecialchars($pr['posologia']) ?></td>
                        <td><?= htmlspecialchars($pr['quantita']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div style="padding: 0 0 20px 0;">
        <a href="index_medico.php" class="btn">← Torna alla Home</a>
    </div>

</div>

</body>
</html>
