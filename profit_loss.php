<?php
include 'db.php';

$accounts = $mysqli->query("SELECT id, name, type FROM accounts ORDER BY type, name")->fetch_all(MYSQLI_ASSOC);
$income = [];
$expense = [];
$total_income = 0;
$total_expense = 0;
foreach ($accounts as $a) {
    if ($a['type'] == 'Revenue' || $a['type'] == 'Expense') {
        $sum = $mysqli->query("SELECT SUM(debit) as debit, SUM(credit) as credit FROM journal_entry_lines WHERE account_id={$a['id']}")->fetch_assoc();
        $bal = floatval($sum['debit']) - floatval($sum['credit']);
        if ($a['type'] == 'Revenue') {
            $amount = -$bal; // revenue: credit > debit
            if (abs($amount) > 0.005) {
                $income[] = ['name'=>$a['name'], 'amount'=>$amount];
                $total_income += $amount;
            }
        } else {
            $amount = $bal; // expense: debit > credit
            if (abs($amount) > 0.005) {
                $expense[] = ['name'=>$a['name'], 'amount'=>$amount];
                $total_expense += $amount;
            }
        }
    }
}
$net = $total_income - $total_expense;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Statement of Profit or Loss</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'nav.php'; ?>
<div class="container">
    <h2>Statement of Profit or Loss</h2>
    <table class="table">
        <tr><th colspan="2">Income</th></tr>
        <?php foreach($income as $i): ?>
        <tr>
            <td><?= htmlspecialchars($i['name']) ?></td>
            <td><?= number_format($i['amount'],2) ?></td>
        </tr>
        <?php endforeach; ?>
        <tr class="totals-row">
            <td><b>Total Income</b></td>
            <td><b><?= number_format($total_income,2) ?></b></td>
        </tr>
        <tr><th colspan="2">Expenses</th></tr>
        <?php foreach($expense as $e): ?>
        <tr>
            <td><?= htmlspecialchars($e['name']) ?></td>
            <td><?= number_format($e['amount'],2) ?></td>
        </tr>
        <?php endforeach; ?>
        <tr class="totals-row">
            <td><b>Total Expenses</b></td>
            <td><b><?= number_format($total_expense,2) ?></b></td>
        </tr>
        <tr class="totals-row">
            <td><b>Net Profit / (Loss)</b></td>
            <td><b><?= number_format($net,2) ?></b></td>
        </tr>
    </table>
</div>
</body>
</html>