<?php
include 'db.php';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$invoice = $mysqli->query("SELECT i.*, c.name as customer_name, c.address, c.contact FROM invoices i JOIN customers c ON i.customer_id = c.id WHERE i.id=$id")->fetch_assoc();
if (!$invoice) {
    echo "<h3>Invoice not found.</h3>";
    exit;
}
$items = $mysqli->query("SELECT ii.*, p.name as product_name FROM invoice_items ii LEFT JOIN products p ON ii.product_id=p.id WHERE ii.invoice_id=$id");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Invoice <?= htmlspecialchars($invoice['invoice_number']) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <style>
        @media print {
            body * { visibility: hidden; }
            #printArea, #printArea * { visibility: visible; }
            #printArea { position: absolute; left: 0; top: 0; width: 100%; }
        }
    </style>
</head>
<body>
<div class="container mt-4" id="printArea">
    <div class="border p-4">
        <h3 class="text-center mb-4">INVOICE</h3>
        <div class="row mb-2">
            <div class="col-6">
                <strong>Customer:</strong> <?= htmlspecialchars($invoice['customer_name']) ?><br>
                <strong>Address:</strong> <?= htmlspecialchars($invoice['address']) ?><br>
                <strong>Contact:</strong> <?= htmlspecialchars($invoice['contact']) ?><br>
                <strong>Invoice Date:</strong> <?= htmlspecialchars($invoice['invoice_date']) ?><br>
                <strong>Due Date:</strong> <?= htmlspecialchars($invoice['due_date']) ?><br>
            </div>
            <div class="col-6 text-end">
                <strong>Invoice #:</strong> <?= htmlspecialchars($invoice['invoice_number']) ?><br>
                <strong>Notes:</strong> <?= htmlspecialchars($invoice['notes']) ?>
            </div>
        </div>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Description</th>
                    <th>Qty</th>
                    <th>Unit Price</th>
                    <th>Line Total</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $subtotal = 0;
                foreach ($items as $item):
                    $line = $item['quantity'] * $item['unit_price'];
                    $subtotal += $line;
                ?>
                <tr>
                    <td><?= htmlspecialchars($item['product_name']) ?></td>
                    <td><?= htmlspecialchars($item['description']) ?></td>
                    <td><?= htmlspecialchars($item['quantity']) ?></td>
                    <td><?= number_format($item['unit_price'],2) ?></td>
                    <td><?= number_format($line,2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" class="text-end">Subtotal</td>
                    <td><?= number_format($subtotal,2) ?></td>
                </tr>
                <tr>
                    <td colspan="4" class="text-end fw-bold">Total Amount</td>
                    <td class="fw-bold"><?= number_format($subtotal,2) ?></td>
                </tr>
            </tfoot>
        </table>
    </div>
    <div class="text-center mt-3">
        <button class="btn btn-primary" onclick="window.print()">Print</button>
    </div>
</div>
</body>
</html>
