<?php
include 'db.php';
// Filters
$where = [];
if (!empty($_GET['customer'])) {
    $cid = (int)$_GET['customer'];
    $where[] = "i.customer_id=$cid";
}
if (!empty($_GET['invno'])) {
    $invno = $mysqli->real_escape_string($_GET['invno']);
    $where[] = "i.invoice_number LIKE '%$invno%'";
}
if (!empty($_GET['from'])) {
    $from = $mysqli->real_escape_string($_GET['from']);
    $where[] = "i.invoice_date >= '$from'";
}
if (!empty($_GET['to'])) {
    $to = $mysqli->real_escape_string($_GET['to']);
    $where[] = "i.invoice_date <= '$to'";
}
if (!empty($_GET['status'])) {
    $status = $mysqli->real_escape_string($_GET['status']);
    if ($status == 'paid') $where[] = "inv_total <= IFNULL(paid_total,0)";
    elseif ($status == 'unpaid') $where[] = "IFNULL(paid_total,0)=0";
    elseif ($status == 'partial') $where[] = "IFNULL(paid_total,0)>0 AND inv_total > IFNULL(paid_total,0)";
}
$where_sql = $where ? 'WHERE '.implode(' AND ', $where) : '';

// Handle delete
if (isset($_POST['delete'])) {
    $id = (int)$_POST['id'];
    $mysqli->query("DELETE FROM invoices WHERE id=$id");
}

// Customers for filter
$customers = $mysqli->query("SELECT id, name FROM customers ORDER BY name")->fetch_all(MYSQLI_ASSOC);

// Invoice list with payment status
$invoices = $mysqli->query("
    SELECT i.*, c.name as customer_name,
        (SELECT SUM(quantity*unit_price) FROM invoice_items WHERE invoice_id=i.id) as inv_total,
        (SELECT SUM(amount) FROM payments WHERE invoice_id=i.id) as paid_total
    FROM invoices i
    JOIN customers c ON i.customer_id = c.id
    $where_sql
    ORDER BY i.id ASC
");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Invoice Management</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <style>
        .btn-pink { background:#e75480; color:#fff; }
        .btn-deepblue { background:#003366; color:#fff; }
        .btn-lightgrey { background:#eee; color:#333; }
    </style>
</head>
<body>
<?php include 'nav.php'; ?>
<div class="container mt-4">
    <h2>Invoice Management</h2>
    <!-- Filter/Search Form -->
    <form class="row g-2 mb-3" method="get">
        <div class="col-md-2">
            <select name="customer" class="form-select">
                <option value="">All Customers</option>
                <?php foreach($customers as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= (isset($_GET['customer']) && $_GET['customer']==$c['id'])?'selected':'' ?>><?= htmlspecialchars($c['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <input type="text" name="invno" class="form-control" placeholder="Invoice #" value="<?= htmlspecialchars($_GET['invno'] ?? '') ?>">
        </div>
        <div class="col-md-2">
            <input type="date" name="from" class="form-control" value="<?= htmlspecialchars($_GET['from'] ?? '') ?>">
        </div>
        <div class="col-md-2">
            <input type="date" name="to" class="form-control" value="<?= htmlspecialchars($_GET['to'] ?? '') ?>">
        </div>
        <div class="col-md-2">
            <select name="status" class="form-select">
                <option value="">All Status</option>
                <option value="paid" <?= (isset($_GET['status']) && $_GET['status']=='paid')?'selected':'' ?>>Paid</option>
                <option value="unpaid" <?= (isset($_GET['status']) && $_GET['status']=='unpaid')?'selected':'' ?>>Unpaid</option>
                <option value="partial" <?= (isset($_GET['status']) && $_GET['status']=='partial')?'selected':'' ?>>Partial</option>
            </select>
        </div>
        <div class="col-md-2">
            <button class="btn btn-primary" type="submit">Filter</button>
            <a href="invoice_management.php" class="btn btn-secondary">Reset</a>
        </div>
    </form>
    <!-- Invoice Table -->
    <table class="table table-bordered table-sm align-middle">
        <tr>
            <th>Invoice #</th>
            <th>Date</th>
            <th>Customer</th>
            <th>Total</th>
            <th>Paid</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
        <?php
        $seq = 1;
        while($inv = $invoices->fetch_assoc()):
            // Generate sequential invoice number as INV001, INV002, etc.
            $seq_no = 'INV' . str_pad($seq, 3, '0', STR_PAD_LEFT);
            $seq++;
            $total = floatval($inv['inv_total']);
            $paid = floatval($inv['paid_total']);
            $status = ($paid >= $total && $total > 0) ? 'Paid' : (($paid > 0) ? 'Partial' : 'Unpaid');
        ?>
        <tr>
            <td><?= htmlspecialchars($seq_no) ?></td>
            <td><?= $inv['invoice_date'] ?></td>
            <td><?= htmlspecialchars($inv['customer_name']) ?></td>
            <td><?= number_format($total,2) ?></td>
            <td><?= number_format($paid,2) ?></td>
            <td>
                <span class="badge bg-<?= $status=='Paid'?'success':($status=='Partial'?'warning text-dark':'danger') ?>">
                    <?= $status ?>
                </span>
            </td>
            <td>
                <a href="invoice_edit.php?id=<?= $inv['id'] ?>" class="btn btn-primary btn-sm">Edit</a>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="id" value="<?= $inv['id'] ?>">
                    <button class="btn btn-danger btn-sm" name="delete" onclick="return confirm('Delete this invoice?')">Delete</button>
                </form>
                <button class="btn btn-lightgrey btn-sm" onclick="printInvoiceRow(<?= $inv['id'] ?>)">Print</button>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>
<script>
function printInvoiceRow(invoiceId) {
    window.open('invoice_print.php?id=' + invoiceId, '_blank');
}
</script>
</body>
</html>