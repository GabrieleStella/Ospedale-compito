<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Ospedale - Dashboard Farmacista</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f4f4f4; }
        header { background: #2c3e50; color: white; padding: 15px; }
        nav { background: #34495e; padding: 10px; }
        nav a { color: white; margin-right: 15px; text-decoration: none; }
        .container { padding: 20px; }
        .card { background: white; padding: 15px; margin-bottom: 15px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { text-align: left; padding: 12px; border-bottom: 1px solid #ddd; }
        th { background-color: #f8f9fa; }
        .status-low { color: #e74c3c; font-weight: bold; }
        .btn-ordina {
            background: #27ae60;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        .btn-ordina:hover { background: #2ecc71; }
    </style>
</head>
<body>

<header>
    <h1>Gestione Ospedale - Area Farmacia</h1>
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
$username   = 'alessandro.betti_app';
$password   = 'om%uVyN@e8yi';
$db_name    = '5DINF_Ospedale_alessandro.betti';

$conn = mysqli_connect($servername, $username, $password, $db_name);

if (!$conn) {
    die("Connessione fallita: " . mysqli_connect_error());
}

$sql = "SELECT id_farmaco, nome, quantita_magazzino FROM FARMACI";
$ris = mysqli_query($conn, $sql);

if (!$ris) {
    die("Errore nella query: " . mysqli_error($conn));
}

// ------------------------------------------------
// Prescrizioni recenti dei medici (ultime 50)
// ------------------------------------------------
$prescrizioni_medici = [];
$sql_pres = "SELECT pr.id_prescrizione, pr.posologia, pr.quantita,
                    p.nome AS pnome, p.cognome AS pcognome,
                    m.nome AS mnome,
                    v.data_ora
             FROM PRESCRIZIONI pr
             JOIN VISITE v   ON pr.id_visita  = v.id_visita
             JOIN PAZIENTI p ON v.id_paziente = p.id_paziente
             JOIN MEDICI m   ON v.id_medico   = m.id_medico
             ORDER BY v.data_ora DESC
             LIMIT 50";
$ris_pres = mysqli_query($conn, $sql_pres);
if ($ris_pres) {
    while ($row = mysqli_fetch_assoc($ris_pres)) {
        $prescrizioni_medici[] = $row;
    }
}

mysqli_close($conn);
?>

<div class="container">
    <div class="card">
        <h2>Benvenuto Farmacista</h2>
        <p>Monitoraggio scorte e gestione ordini farmaci.</p>
    </div>

    <div class="card">
        <h3>Stato Inventario Farmaci</h3>
        <table>
            <thead>
                <tr>
                    <th>Farmaco</th>
                    <th>Quantità</th>
                    <th>Azione</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($farmaco = mysqli_fetch_assoc($ris)): ?>
                <tr>
                    <td><?= htmlspecialchars($farmaco['nome']) ?></td>
                    <td class="<?= $farmaco['quantita_magazzino'] <= 20 ? 'status-low' : '' ?>">
                        <?= htmlspecialchars($farmaco['quantita_magazzino']) ?> unità
                        <?= $farmaco['quantita_magazzino'] <= 20 ? '(Scarse)' : '' ?>
                    </td>
                    <td>
                        <button class="btn-ordina"
                            onclick="window.location.href='ordina.php?farmaco=<?= urlencode($farmaco['nome']) ?>'">
                            Ordina
                        </button>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    </div>

    <!-- Prescrizioni richieste dai medici -->
    <div class="card">
        <h3>Richieste Farmaci dai Medici</h3>
        <table>
            <thead>
                <tr>
                    <th>Data Visita</th>
                    <th>Medico</th>
                    <th>Paziente</th>
                    <th>Farmaco / Posologia</th>
                    <th>Quantità Richiesta</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($prescrizioni_medici)): ?>
                <tr>
                    <td colspan="5" style="color:#999; font-style:italic;">Nessuna prescrizione registrata.</td>
                </tr>
                <?php else: ?>
                    <?php foreach ($prescrizioni_medici as $pr): ?>
                    <tr>
                        <td><?= date('d/m/Y H:i', strtotime($pr['data_ora'])) ?></td>
                        <td><?= htmlspecialchars($pr['mnome']) ?></td>
                        <td><?= htmlspecialchars($pr['pcognome'] . ' ' . $pr['pnome']) ?></td>
                        <td><?= htmlspecialchars($pr['posologia']) ?></td>
                        <td><?= htmlspecialchars($pr['quantita']) ?> unità</td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
