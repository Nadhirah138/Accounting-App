<?php
include 'db.php';

$accounts = $mysqli->query("SELECT id, name, type FROM accounts ORDER BY type, name")->fetch_all(MYSQLI_ASSOC);
$assets = [];
$liabilities = [];
$equity = [];
$total_assets = 0;
$total_liabilities = 0;
$total_equity = 0;
$withdrawal = 0;
foreach ($accounts as $a) {
    $sum = $mysqli->query("SELECT SUM(debit) as debit, SUM(credit) as credit FROM journal_entry_lines WHERE account_id={$a['id']}")->fetch_assoc();
    $bal = floatval($sum['debit']) - floatval($sum['credit']);
    if ($a['type'] == 'Asset') {
        if (abs($bal) > 0.005) {
            $assets[] = ['name'=>$a['name'], 'amount'=>$bal];
            $total_assets += $bal;
        }
    } elseif ($a['type'] == 'Liability') {
        if (abs($bal) > 0.005) {
            $liabilities[] = ['name'=>$a['name'], 'amount'=>abs($bal)];
            $total_liabilities += abs($bal);
        }
    } elseif ($a['type'] == 'Equity') {
        if (strtolower($a['name']) == 'withdrawal') {
            $withdrawal += $bal;
        } else {
            if (abs($bal) > 0.005) {
                $equity[] = ['name'=>$a['name'], 'amount'=>abs($bal)];
                $total_equity += abs($bal);
            }
        }
    }
}
// Calculate net profit/loss
$income = 0; $expense = 0;
foreach ($accounts as $a) {
    if ($a['type'] == 'Revenue') {
        $sum = $mysqli->query("SELECT SUM(debit) as debit, SUM(credit) as credit FROM journal_entry_lines WHERE account_id={$a['id']}")->fetch_assoc();
        $bal = floatval($sum['debit']) - floatval($sum['credit']);
        $income += -$bal;
    } elseif ($a['type'] == 'Expense') {
        $sum = $mysqli->query("SELECT SUM(debit) as debit, SUM(credit) as credit FROM journal_entry_lines WHERE account_id={$a['id']}")->fetch_assoc();
        $bal = floatval($sum['debit']) - floatval($sum['credit']);
        $expense += $bal;
    }
}
$net_profit = $income - $expense;
$final_equity = $total_equity + $net_profit - $withdrawal;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Statement of Financial Position</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'nav.php'; ?>
<div class="container">
    <h2>Statement of Financial Position</h2>
    <table class="table" style="width:400px;">
        <tr><th colspan="2">Assets</th></tr>
        <?php foreach($assets as $a): ?>
        <tr>
            <td><?= htmlspecialchars($a['name']) ?></td>
            <td><?= number_format($a['amount'],2) ?></td>
        </tr>
        <?php endforeach; ?>
        <tr class="totals-row">
            <td><b>Total Assets</b></td>
            <td><b><?= number_format($total_assets,2) ?></b></td>
        </tr>
        <tr><th colspan="2">Liabilities</th></tr>
        <?php foreach($liabilities as $l): ?>
        <tr>
            <td><?= htmlspecialchars($l['name']) ?></td>
            <td><?= number_format($l['amount'],2) ?></td>
        </tr>
        <?php endforeach; ?>
        <tr class="totals-row">
            <td><b>Total Liabilities</b></td>
            <td><b><?= number_format($total_liabilities,2) ?></b></td>
        </tr>
        <tr><th colspan="2">Equity</th></tr>
        <?php foreach($equity as $e): ?>
        <tr>
            <td><?= htmlspecialchars($e['name']) ?></td>
            <td><?= number_format($e['amount'],2) ?></td>
        </tr>
        <?php endforeach; ?>
        <tr>
            <td>+ Net Profit/Loss</td>
            <td><?= number_format($net_profit,2) ?></td>
        </tr>
        <tr>
            <td>- Withdrawal</td>
            <td><?= number_format($withdrawal,2) ?></td>
        </tr>
        <tr class="totals-row">
            <td><b>Total Equity</b></td>
            <td><b><?= number_format($final_equity,2) ?></b></td>
        </tr>
        <tr class="totals-row">
            <td><b>Total Liabilities & Equity</b></td>
            <td><b><?= number_format($total_liabilities + $final_equity,2) ?></b></td>
        </tr>
    </table>
</div>
</body>
</html>