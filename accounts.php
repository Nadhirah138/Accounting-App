<?php
include 'db.php';
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add'])) {
        $name = $mysqli->real_escape_string($_POST['name']);
        $type = $mysqli->real_escape_string($_POST['type']);
        $mysqli->query("INSERT INTO accounts (name, type) VALUES ('$name', '$type')");
        $msg = $mysqli->affected_rows ? 'Account added.' : 'Failed to add account.';
    }
    if (isset($_POST['edit'])) {
        $id = (int)$_POST['id'];
        $name = $mysqli->real_escape_string($_POST['name']);
        $type = $mysqli->real_escape_string($_POST['type']);
        $mysqli->query("UPDATE accounts SET name='$name', type='$type' WHERE id=$id");
        $msg = $mysqli->affected_rows ? 'Account updated.' : 'Failed to update account.';
    }
    if (isset($_POST['delete'])) {
        $id = (int)$_POST['id'];
        $mysqli->query("DELETE FROM accounts WHERE id=$id");
        $msg = $mysqli->affected_rows ? 'Account deleted.' : 'Failed to delete account.';
    }
}
$accounts = $mysqli->query("SELECT * FROM accounts ORDER BY id");
$edit = null;
if (isset($_GET['edit'])) {
    $eid = (int)$_GET['edit'];
    $edit = $mysqli->query("SELECT * FROM accounts WHERE id=$eid")->fetch_assoc();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Accounts</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'nav.php'; ?>
<div class="container">
    <h2>Accounts</h2>
    <?php if ($msg): ?>
        <div class="alert-success"><?= $msg ?></div>
    <?php endif; ?>
    <form method="post" style="margin-bottom:20px;">
        <input type="hidden" name="id" value="<?= $edit['id'] ?? '' ?>">
        <input type="text" name="name" placeholder="Account Name" required value="<?= $edit['name'] ?? '' ?>">
        <select name="type" required>
            <option value="">Type</option>
            <?php foreach(['Asset','Liability','Equity','Revenue','Expense'] as $t): ?>
                <option value="<?= $t ?>" <?= (isset($edit['type']) && $edit['type']==$t)?'selected':'' ?>><?= $t ?></option>
            <?php endforeach; ?>
        </select>
        <?php if ($edit): ?>
            <button class="btn btn-blue" name="edit">Update</button>
            <a href="accounts.php" class="btn btn-gray">Cancel</a>
        <?php else: ?>
            <button class="btn btn-green" name="add">Add Account</button>
        <?php endif; ?>
    </form>
    <table class="table">
        <tr><th>Name</th><th>Type</th><th>Actions</th></tr>
        <?php while($a = $accounts->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($a['name']) ?></td>
            <td><?= $a['type'] ?></td>
            <td>
                <a href="?edit=<?= $a['id'] ?>" class="btn btn-blue">Edit</a>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="id" value="<?= $a['id'] ?>">
                    <button class="btn btn-red" name="delete" onclick="return confirm('Delete this account?')">Delete</button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>
</body>
</html>
