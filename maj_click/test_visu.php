<?php
include_once(__DIR__ . '/../fonctions.inc.php');
include_once(__DIR__ . '/../config.php');
$pdo = connect();

$query = "SELECT * FROM uid_table";
$stmt = $pdo->prepare($query);
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Affichage des Données</title>
    <style>
        table, th, td {
            border: 1px solid black;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h1>Données reçues</h1>
    <table>
        <thead>
            <tr>
                <th>UID</th>
                <th>URL Datas</th>
                <th>Body Datas</th>
                <th>Image</th>
                <th>Cookies</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($results as $row): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['uid']); ?></td>
                <td style="max-width: 300px;"><?php echo htmlspecialchars($row['url_datas']); ?></td>
                <td style="max-width: 900px;"><p><?php echo htmlspecialchars($row['body_datas']); ?></p></td>
                <td>
                    <?php if (!empty($row['other_datas'])): ?>
                        <img src="data:image/png;base64,<?php echo base64_encode(hex2bin($row['other_datas'])); ?>" alt="Image" style="max-width: 100px; max-height: 100px;">
                    <?php endif; ?>
                </td>
                <td><?php echo htmlspecialchars($row['cookies']); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>