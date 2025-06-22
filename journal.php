<?php
include 'db.php';
$msg = '';
$alert_class = 'alert-success';
// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['journal_submit'])) {
    $date = $mysqli->real_escape_string($_POST['entry_date']);
    $desc = $mysqli->real_escape_string($_POST['description']);
    $accounts = $_POST['account'] ?? [];
    $debits = $_POST['debit'] ?? [];
    $credits = $_POST['credit'] ?? [];
    $lines = [];
    $total_debit = 0; $total_credit = 0;
    for ($i=0; $i<count($accounts); $i++) {
        $aid = (int)$accounts[$i];
        $d = floatval($debits[$i]);
        $c = floatval($credits[$i]);
        if ($aid && ($d > 0 || $c > 0)) {
            $lines[] = [$aid, $d, $c];
            $total_debit += $d;
            $total_credit += $c;
        }
    }
    if ($total_debit != $total_credit) {
        $msg = "Total debit and credit must be equal!";
        $alert_class = 'alert-error';
    } elseif (count($lines) < 2) {
        $msg = "At least two accounts required.";
        $alert_class = 'alert-error';
    } else {
        $mysqli->begin_transaction();
        try {
            $mysqli->query("INSERT INTO journal_entries (entry_date, description) VALUES ('$date', '$desc')");
            $jid = $mysqli->insert_id;
            foreach ($lines as $l) {
                $mysqli->query("INSERT INTO journal_entry_lines (journal_entry_id, account_id, debit, credit) VALUES ($jid, $l[0], $l[1], $l[2])");
            }
            $mysqli->commit();
            $msg = "Journal entry saved.";
        } catch (Exception $e) {
            $mysqli->rollback();
            $msg = "Failed to save entry: " . $e->getMessage();
            $alert_class = 'alert-error';
        }
    }
    header("Refresh:1");
}
if (isset($_POST['delete_entry'])) {
    $jid = (int)$_POST['jid'];
    $mysqli->query("DELETE FROM journal_entries WHERE id=$jid");
    $msg = "Entry deleted.";
    header("Refresh:1");
}
$accounts = $mysqli->query("SELECT * FROM accounts ORDER BY name")->fetch_all(MYSQLI_ASSOC);
$entries = $mysqli->query("
    SELECT je.*, 
        GROUP_CONCAT(CASE WHEN jel.debit>0 THEN a.name END ORDER BY jel.id SEPARATOR ', ') AS debit_acc,
        GROUP_CONCAT(CASE WHEN jel.debit>0 THEN jel.debit END ORDER BY jel.id SEPARATOR ', ') AS debit_amt,
        GROUP_CONCAT(CASE WHEN jel.credit>0 THEN a.name END ORDER BY jel.id SEPARATOR ', ') AS credit_acc,
        GROUP_CONCAT(CASE WHEN jel.credit>0 THEN jel.credit END ORDER BY jel.id SEPARATOR ', ') AS credit_amt
    FROM journal_entries je
    JOIN journal_entry_lines jel ON je.id=jel.journal_entry_id
    JOIN accounts a ON jel.account_id=a.id
    GROUP BY je.id
    ORDER BY je.entry_date DESC, je.id DESC
    LIMIT 30
");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Journal Entries</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .row-btn { font-size: 0.9rem; padding: 4px 10px; }
        .icon-trash:before { content: "\1F5D1"; }
        .icon-check:before { content: "\2714"; color: #27ae60; }
        .icon-cross:before { content: "\2716"; color: #e74c3c; }
        .totals-row { font-weight: bold; background: #f0f0f0; }
    </style>
</head>
<body>
<?php include 'nav.php'; ?>
<div class="container">
    <h2>Journal Entry</h2>
    <?php if ($msg): ?>
        <div class="<?= $alert_class ?>"><?= $msg ?></div>
    <?php endif; ?>
    <form method="post" id="journalForm" autocomplete="off">
        <div>
            <label>Date: <input type="date" name="entry_date" value="<?= date('Y-m-d') ?>"></label>
        </div>
        <div>
            <label>Description (optional):<br>
                <textarea name="description" rows="3" style="width:100%;"></textarea>
            </label>
        </div>
        <table class="table" id="journalTable">
            <thead>
                <tr>
                    <th>Account</th>
                    <th>Debit</th>
                    <th>Credit</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <select name="account[]">
                            <option value="">Select</option>
                            <?php foreach($accounts as $a): ?>
                                <option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td><input type="number" name="debit[]" step="0.01" min="0" value="0"></td>
                    <td><input type="number" name="credit[]" step="0.01" min="0" value="0"></td>
                    <td>
                        <button type="button" class="btn btn-red row-btn remove-row" title="Remove"><span class="icon-trash"></span></button>
                    </td>
                </tr>
            </tbody>
            <tfoot>
                <tr class="totals-row">
                    <td>Total</td>
                    <td id="totalDebit">0.00</td>
                    <td id="totalCredit">0.00</td>
                    <td id="balanceStatus"></td>
                </tr>
            </tfoot>
        </table>
        <button type="button" class="btn btn-green" id="addRow"><b>+</b> Add Account</button>
        <button type="submit" class="btn btn-blue" name="journal_submit" style="font-size:1.1rem;margin-left:20px;">
            <span class="icon-check"></span> Save Transaction
        </button>
    </form>
    <script>
    function updateTotals() {
        let totalDebit = 0, totalCredit = 0;
        document.querySelectorAll('input[name="debit[]"]').forEach(e => totalDebit += parseFloat(e.value||0));
        document.querySelectorAll('input[name="credit[]"]').forEach(e => totalCredit += parseFloat(e.value||0));
        document.getElementById('totalDebit').textContent = totalDebit.toFixed(2);
        document.getElementById('totalCredit').textContent = totalCredit.toFixed(2);
        let status = document.getElementById('balanceStatus');
        if (totalDebit === totalCredit && totalDebit > 0) {
            status.innerHTML = '<span class="icon-check"></span>';
        } else {
            status.innerHTML = '<span class="icon-cross"></span>';
        }
    }
    document.getElementById('journalTable').addEventListener('input', updateTotals);
    document.getElementById('addRow').onclick = function() {
        let row = document.querySelector('#journalTable tbody tr').cloneNode(true);
        row.querySelectorAll('input,select').forEach(e => { if(e.tagName=='INPUT')e.value=0; else e.selectedIndex=0; });
        document.querySelector('#journalTable tbody').appendChild(row);
    };
    document.getElementById('journalTable').addEventListener('click', function(e) {
        if (e.target.closest('.remove-row')) {
            let rows = document.querySelectorAll('#journalTable tbody tr');
            if (rows.length > 1) e.target.closest('tr').remove();
            updateTotals();
        }
    });
    document.getElementById('journalForm').onsubmit = function() {
        let td = parseFloat(document.getElementById('totalDebit').textContent);
        let tc = parseFloat(document.getElementById('totalCredit').textContent);
        if (td !== tc || td === 0) {
            alert('Total debit and credit must be equal and greater than zero!');
            return false;
        }
        return true;
    };
    updateTotals();
    </script>
    <h2>Recent Transactions</h2>
    <table class="table">
        <tr>
            <th>Date</th>
            <th>Debit Account</th>
            <th>Debit Amount</th>
            <th>Credit Account</th>
            <th>Credit Amount</th>
            <th>Description</th>
            <th>Actions</th>
        </tr>
        <?php while($e = $entries->fetch_assoc()): ?>
        <tr>
            <td><?= $e['entry_date'] ?></td>
            <td><?= htmlspecialchars($e['debit_acc']) ?></td>
            <td><?= htmlspecialchars($e['debit_amt']) ?></td>
            <td><?= htmlspecialchars($e['credit_acc']) ?></td>
            <td><?= htmlspecialchars($e['credit_amt']) ?></td>
            <td><?= htmlspecialchars(mb_strimwidth($e['description'],0,30,'...')) ?></td>
            <td>
                <!-- Edit functionality can be implemented similarly -->
                <form method="post" style="display:inline;">
                    <input type="hidden" name="jid" value="<?= $e['id'] ?>">
                    <button class="btn btn-red" name="delete_entry" onclick="return confirm('Delete this entry?')"><span class="icon-trash"></span></button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>
</body>
</html>
