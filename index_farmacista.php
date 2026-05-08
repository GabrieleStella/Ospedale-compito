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

</body>
</html>
