<?php
include 'db.php';
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add'])) {
        $code = $mysqli->real_escape_string($_POST['product_code']);
        $name = $mysqli->real_escape_string($_POST['name']);
        $desc = $mysqli->real_escape_string($_POST['description']);
        $price = floatval($_POST['unit_price']);
        $tax = floatval($_POST['tax_rate']);
        $mysqli->query("INSERT INTO products (product_code, name, description, unit_price, tax_rate) VALUES ('$code', '$name', '$desc', $price, $tax)");
        $msg = 'Product added!';
    }
    if (isset($_POST['edit'])) {
        $id = (int)$_POST['id'];
        $code = $mysqli->real_escape_string($_POST['product_code']);
        $name = $mysqli->real_escape_string($_POST['name']);
        $desc = $mysqli->real_escape_string($_POST['description']);
        $price = floatval($_POST['unit_price']);
        $tax = floatval($_POST['tax_rate']);
        $mysqli->query("UPDATE products SET product_code='$code', name='$name', description='$desc', unit_price=$price, tax_rate=$tax WHERE id=$id");
        $msg = 'Product updated!';
    }
    if (isset($_POST['delete'])) {
        $id = (int)$_POST['id'];
        $mysqli->query("DELETE FROM products WHERE id=$id");
        $msg = 'Product deleted!';
    }
}
$products = $mysqli->query("SELECT * FROM products ORDER BY id DESC");
$edit = null;
if (isset($_GET['edit'])) {
    $eid = (int)$_GET['edit'];
    $edit = $mysqli->query("SELECT * FROM products WHERE id=$eid")->fetch_assoc();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Products</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body>
<?php include 'nav.php'; ?>
<div class="container mt-4">
    <h2>Products</h2>
    <?php if ($msg): ?>
        <div class="alert alert-success"><?= $msg ?></div>
    <?php endif; ?>
    <form method="post" class="row g-2 mb-3">
        <input type="hidden" name="id" value="<?= $edit['id'] ?? '' ?>">
        <div class="col-md-2">
            <input type="text" name="product_code" class="form-control" placeholder="Product Code" value="<?= $edit['product_code'] ?? '' ?>">
        </div>
        <div class="col-md-2">
            <input type="text" name="name" class="form-control" placeholder="Name" required value="<?= $edit['name'] ?? '' ?>">
        </div>
        <div class="col-md-3">
            <input type="text" name="description" class="form-control" placeholder="Description" value="<?= $edit['description'] ?? '' ?>">
        </div>
        <div class="col-md-2">
            <input type="number" name="unit_price" class="form-control" placeholder="Unit Price" step="0.01" required value="<?= $edit['unit_price'] ?? '' ?>">
        </div>
        <div class="col-md-1">
            <input type="number" name="tax_rate" class="form-control" placeholder="Tax %" step="0.01" value="<?= $edit['tax_rate'] ?? '0' ?>">
        </div>
        <div class="col-md-2">
        <?php if ($edit): ?>
            <button class="btn btn-primary" name="edit" type="submit">Save</button>
            <a href="products.php" class="btn btn-secondary">Cancel</a>
        <?php else: ?>
            <button class="btn btn-success" name="add" type="submit">Add Product</button>
        <?php endif; ?>
        </div>
    </form>
    <table class="table table-bordered">
        <tr>
            <th>Code</th><th>Name</th><th>Description</th><th>Unit Price</th><th>Tax %</th><th>Actions</th>
        </tr>
        <?php while($p = $products->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($p['product_code']) ?></td>
            <td><?= htmlspecialchars($p['name']) ?></td>
            <td><?= htmlspecialchars($p['description']) ?></td>
            <td><?= number_format($p['unit_price'],2) ?></td>
            <td><?= number_format($p['tax_rate'],2) ?></td>
            <td>
                <a href="?edit=<?= $p['id'] ?>" class="btn btn-primary btn-sm">Edit</a>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="id" value="<?= $p['id'] ?>">
                    <button class="btn btn-danger btn-sm" name="delete" onclick="return confirm('Delete this product?')">Delete</button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>
</body>
</html>