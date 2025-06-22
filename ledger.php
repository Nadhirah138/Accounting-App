<?php
include 'db.php';
$accounts = $mysqli->query("SELECT * FROM accounts ORDER BY name")->fetch_all(MYSQLI_ASSOC);
$aid = isset($_GET['account']) ? (int)$_GET['account'] : $accounts[0]['id'];
$acc = array_filter($accounts, fn($a) => $a['id']==$aid);
$acc = $acc ? array_values($acc)[0] : $accounts[0];
$lines = $mysqli->query("
    SELECT je.entry_date, je.description, jel.debit, jel.credit
    FROM journal_entry_lines jel
    JOIN journal_entries je ON jel.journal_entry_id=je.id
    WHERE jel.account_id=$aid
    ORDER BY je.entry_date, jel.id
");
$balance = 0;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Ledger</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'nav.php'; ?>
<div class="container">
    <h2>Ledger</h2>
    <form method="get">
        <select name="account" onchange="this.form.submit()">
            <?php foreach($accounts as $a): ?>
                <option value="<?= $a['id'] ?>" <?= $a['id']==$aid?'selected':'' ?>><?= htmlspecialchars($a['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </form>
    <table class="table">
        <tr>
            <th>Date</th>
            <th>Particulars</th>
            <th>Debit</th>
            <th>Credit</th>
            <th>Balance</th>
        </tr>
        <?php foreach($lines as $l): 
            $balance += $l['debit'] - $l['credit'];
        ?>
        <tr>
            <td><?= $l['entry_date'] ?></td>
            <td><?= htmlspecialchars($l['description']) ?></td>
            <td><?= $l['debit'] ? number_format($l['debit'],2) : '' ?></td>
            <td><?= $l['credit'] ? number_format($l['credit'],2) : '' ?></td>
            <td><?= number_format($balance,2) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>
</body>
</html>
