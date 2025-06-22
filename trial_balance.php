<?php
include 'db.php';

$accounts = $mysqli->query("SELECT id, name, type FROM accounts ORDER BY name")->fetch_all(MYSQLI_ASSOC);
$balances = [];
foreach ($accounts as $a) {
    $id = $a['id'];
    $sum = $mysqli->query("SELECT 
        SUM(debit) as debit, SUM(credit) as credit 
        FROM journal_entry_lines WHERE account_id=$id
    ")->fetch_assoc();
    $bal = floatval($sum['debit']) - floatval($sum['credit']);
    if (abs($bal) > 0.005) { // skip zero balances
        $balances[] = [
            'name' => $a['name'],
            'type' => $a['type'],
            'debit' => $bal > 0 ? $bal : 0,
            'credit' => $bal < 0 ? abs($bal) : 0
        ];
    }
}
$total_debit = array_sum(array_column($balances, 'debit'));
$total_credit = array_sum(array_column($balances, 'credit'));
?>
<!DOCTYPE html>
<html>
<head>
    <title>Trial Balance</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'nav.php'; ?>
<div class="container">
    <h2>Trial Balance</h2>
    <table class="table">
        <tr>
            <th>Account</th>
            <th>Debit</th>
            <th>Credit</th>
        </tr>
        <?php foreach($balances as $b): ?>
        <tr>
            <td><?= htmlspecialchars($b['name']) ?></td>
            <td><?= $b['debit'] ? number_format($b['debit'],2) : '' ?></td>
            <td><?= $b['credit'] ? number_format($b['credit'],2) : '' ?></td>
        </tr>
        <?php endforeach; ?>
        <tr class="totals-row">
            <td><b>Total</b></td>
            <td><b><?= number_format($total_debit,2) ?></b></td>
            <td><b><?= number_format($total_credit,2) ?></b></td>
        </tr>
    </table>
</div>
</body>
</html>