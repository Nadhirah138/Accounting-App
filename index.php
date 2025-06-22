<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Accounting</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f7f7f7;
      margin: 0;
      padding: 0;
    }
    .dashboard-container {
      max-width: 1100px;
      margin: 40px auto;
      padding: 30px 10px;
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.07);
    }
    .dashboard-title {
      text-align: center;
      margin-bottom: 30px;
      color: #333;
      font-size: 2.1rem;
      font-weight: bold;
    }
    .dashboard-grid {
      display: flex;
      flex-wrap: wrap;
      gap: 32px;
      justify-content: center;
    }
    .big-btn {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      width: 200px;
      height: 160px;
      font-size: 1.25rem;
      border-radius: 16px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.08);
      text-decoration: none;
      color: #fff;
      font-weight: bold;
      transition: transform 0.12s, box-shadow 0.12s;
      border: none;
      outline: none;
    }
    .big-btn i {
      font-size: 3rem;
      margin-bottom: 18px;
    }
    .big-btn.accounts { background: #27ae60; }
    .big-btn.ledger { background: #2980b9; }
    .big-btn.journal { background: #8e44ad; }
    .big-btn.financial { background: #e67e22; }
    .big-btn.trial { background: #16a085; }
    .big-btn.profit { background: #c0392b; }
    .big-btn.customers { background: #34495e; }
    .big-btn.products { background: #d35400; }
    .big-btn.invoice { background: #2c3e50; }
    .big-btn:hover {
      transform: scale(1.04);
      box-shadow: 0 4px 16px rgba(0,0,0,0.13);
      opacity: 0.93;
    }
    @media (max-width: 700px) {
      .dashboard-grid { gap: 18px; }
      .big-btn { width: 100%; min-width: 180px; height: 120px; font-size: 1.1rem; }
      .big-btn i { font-size: 2rem; margin-bottom: 10px; }
    }
  </style>
</head>
<body>
  <div class="dashboard-container">
    <div class="dashboard-title">Accounting Dashboard</div>
    <div class="dashboard-grid">
      <a href="accounts.php" class="big-btn accounts"><i class="fa fa-list"></i> Accounts</a>
      <a href="ledger.php" class="big-btn ledger"><i class="fa fa-table"></i> Ledger</a>
      <a href="journal.php" class="big-btn journal"><i class="fa fa-book"></i> Journal</a>
      <a href="financial_position.php" class="big-btn financial"><i class="fa fa-balance-scale"></i> Financial Position</a>
      <a href="trial_balance.php" class="big-btn trial"><i class="fa fa-balance-scale-left"></i> Trial Balance</a>
      <a href="profit_loss.php" class="big-btn profit"><i class="fa fa-chart-line"></i> Profit/Loss</a>
      <a href="customers.php" class="big-btn customers"><i class="fa fa-user"></i> Customers</a>
      <a href="products.php" class="big-btn products"><i class="fa fa-cube"></i> Products</a>
      <a href="invoice_management.php" class="big-btn invoice"><i class="fa fa-file-invoice"></i> Invoice Management</a>
    </div>
  </div>
</body>
</html>
