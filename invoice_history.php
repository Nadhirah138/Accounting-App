<?php
include 'db.php';

if (isset($_POST['delete'])) {
    $id = (int)$_POST['id'];
    $mysqli->query("DELETE FROM invoices WHERE id=$id");
}
$invoices = $mysqli->query("
    SELECT i.*, c.name as customer_name
    FROM invoices i
    JOIN customers c ON i.customer_id = c.id
    ORDER BY i.created_at DESC
");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Invoice History</title>
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
    <h2>Invoice History</h2>
    <table class="table table-bordered">
        <tr>
            <th>Invoice #</th>
            <th>Date</th>
            <th>Customer</th>
            <th>Due</th>
            <th>Notes</th>
            <th>Actions</th>
        </tr>
        <?php while($inv = $invoices->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($inv['invoice_number']) ?></td>
            <td><?= $inv['invoice_date'] ?></td>
            <td><?= htmlspecialchars($inv['customer_name']) ?></td>
            <td><?= $inv['due_date'] ?></td>
            <td><?= htmlspecialchars($inv['notes']) ?></td>
            <td>
                <a href="invoice_view.php?id=<?= $inv['id'] ?>" class="btn btn-lightgrey btn-sm">View</a>
                <a href="invoice_edit.php?id=<?= $inv['id'] ?>" class="btn btn-primary btn-sm">Edit</a>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="id" value="<?= $inv['id'] ?>">
                    <button class="btn btn-danger btn-sm" name="delete" onclick="return confirm('Delete this invoice?')">Delete</button>
                </form>
                <a href="invoice_payment.php?id=<?= $inv['id'] ?>" class="btn btn-pink btn-sm">Record Payment</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>
</body>
</html>