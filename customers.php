<?php
include 'db.php';
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add
    if (isset($_POST['add'])) {
        $name = $mysqli->real_escape_string($_POST['name']);
        $address = $mysqli->real_escape_string($_POST['address']);
        $contact = $mysqli->real_escape_string($_POST['contact']);
        $mysqli->query("INSERT INTO customers (name, address, contact) VALUES ('$name', '$address', '$contact')");
        $msg = 'Customer added!';
    }
    // Edit
    if (isset($_POST['edit'])) {
        $id = (int)$_POST['id'];
        $name = $mysqli->real_escape_string($_POST['name']);
        $address = $mysqli->real_escape_string($_POST['address']);
        $contact = $mysqli->real_escape_string($_POST['contact']);
        $mysqli->query("UPDATE customers SET name='$name', address='$address', contact='$contact' WHERE id=$id");
        $msg = 'Customer updated!';
    }
    // Delete
    if (isset($_POST['delete'])) {
        $id = (int)$_POST['id'];
        $mysqli->query("DELETE FROM customers WHERE id=$id");
        $msg = 'Customer deleted!';
    }
}
$customers = $mysqli->query("SELECT * FROM customers ORDER BY id DESC");
$edit = null;
if (isset($_GET['edit'])) {
    $eid = (int)$_GET['edit'];
    $edit = $mysqli->query("SELECT * FROM customers WHERE id=$eid")->fetch_assoc();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Customers</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body>
<?php include 'nav.php'; ?>
<div class="container mt-4">
    <h2>Customers</h2>
    <?php if ($msg): ?>
        <div class="alert alert-success"><?= $msg ?></div>
    <?php endif; ?>
    <form method="post" class="row g-2 mb-3">
        <input type="hidden" name="id" value="<?= $edit['id'] ?? '' ?>">
        <div class="col-md-3">
            <input type="text" name="name" class="form-control" placeholder="Name" required value="<?= $edit['name'] ?? '' ?>">
        </div>
        <div class="col-md-4">
            <input type="text" name="address" class="form-control" placeholder="Address" value="<?= $edit['address'] ?? '' ?>">
        </div>
        <div class="col-md-3">
            <input type="text" name="contact" class="form-control" placeholder="Contact" value="<?= $edit['contact'] ?? '' ?>">
        </div>
        <div class="col-md-2">
        <?php if ($edit): ?>
            <button class="btn btn-primary" name="edit" type="submit">Save</button>
            <a href="customers.php" class="btn btn-secondary">Cancel</a>
        <?php else: ?>
            <button class="btn btn-success" name="add" type="submit">Add Customer</button>
        <?php endif; ?>
        </div>
    </form>
    <table class="table table-bordered">
        <tr>
            <th>Name</th><th>Address</th><th>Contact</th><th>Actions</th>
        </tr>
        <?php while($c = $customers->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($c['name']) ?></td>
            <td><?= htmlspecialchars($c['address']) ?></td>
            <td><?= htmlspecialchars($c['contact']) ?></td>
            <td>
                <a href="?edit=<?= $c['id'] ?>" class="btn btn-primary btn-sm">Edit</a>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="id" value="<?= $c['id'] ?>">
                    <button class="btn btn-danger btn-sm" name="delete" onclick="return confirm('Delete this customer?')">Delete</button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>
</body>
</html>