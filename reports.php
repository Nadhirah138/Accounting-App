<?php
include 'db.php';

// Invoices by month
$by_month = $mysqli->query("
    SELECT DATE_FORMAT(invoice_date,'%Y-%m') as month, COUNT(*) as count, 
    SUM((SELECT SUM(quantity*unit_price + quantity*unit_price*tax_rate/100) FROM invoice_items WHERE invoice_id=invoices.id)) as total
    FROM invoices
    GROUP BY month
    ORDER BY month DESC
");

// Unpaid invoices
$unpaid = $mysqli->query("
    SELECT i.invoice_number, i.invoice_date, c.name as customer, 
    (SELECT SUM(quantity*unit_price + quantity*unit_price*tax_rate/100) FROM invoice_items WHERE invoice_id=i.id) as total,
    (SELECT SUM(amount) FROM payments WHERE invoice_id=i.id) as paid
    FROM invoices i
    JOIN customers c ON i.customer_id = c.id
    HAVING (paid IS NULL OR paid < total)
    ORDER BY i.invoice_date DESC
");

// Total sales
$total_sales = $mysqli->query("
    SELECT SUM(quantity*unit_price + quantity*unit_price*tax_rate/100) as total
    FROM invoice_items
")->fetch_assoc()['total'] ?? 0;

// Export (CSV/Excel/PDF) - just CSV for demo
if (isset($_GET['export']) && $_GET['export']=='csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename=unpaid_invoices.csv');
    echo "Invoice #,Date,Customer,Total,Paid\n";
    $unpaid->data_seek(0);
    while($row = $unpaid->fetch_assoc()) {
        echo "{$row['invoice_number']},{$row['invoice_date']},{$row['customer']},".number_format($row['total'],2).",".number_format($row['paid'],2)."\n";
    }
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Reports</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body>
<?php include 'nav.php'; ?>
<div class="container mt-4">
    <h2>Reports</h2>
    <h4>Invoices by Month</h4>
    <table class="table table-bordered">
        <tr><th>Month</th><th>Count</th><th>Total</th></tr>
        <?php foreach($by_month as $bm): ?>
        <tr>
            <td><?= $bm['month'] ?></td>
            <td><?= $bm['count'] ?></td>
            <td><?= number_format($bm['total'],2) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
    <h4>Unpaid Invoices <a href="?export=csv" class="btn btn-success btn-sm">Export CSV</a></h4>
    <table class="table table-bordered">
        <tr><th>Invoice #</th><th>Date</th><th>Customer</th><th>Total</th><th>Paid</th></tr>
        <?php foreach($unpaid as $u): ?>
        <tr>
            <td><?= $u['invoice_number'] ?></td>
            <td><?= $u['invoice_date'] ?></td>
            <td><?= htmlspecialchars($u['customer']) ?></td>
            <td><?= number_format($u['total'],2) ?></td>
            <td><?= number_format($u['paid'],2) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
    <h4>Total Sales: <?= number_format($total_sales,2) ?></h4>
</div>
</body>
</html>