CREATE DATABASE IF NOT EXISTS accounting;
USE accounting;

CREATE TABLE IF NOT EXISTS accounts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    type ENUM('Asset','Liability','Equity','Revenue','Expense') NOT NULL
);

INSERT INTO accounts (name, type) VALUES
('Cash', 'Asset'),
('Bank', 'Asset'),
('Capital', 'Equity'),
('Receivables', 'Asset'),
('Payables', 'Liability'),
('Withdrawal', 'Equity'),
('Sales', 'Revenue'),
('Purchases', 'Expense');

CREATE TABLE IF NOT EXISTS journal_entries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    entry_date DATE NOT NULL,
    description TEXT
);

CREATE TABLE IF NOT EXISTS journal_entry_lines (
    id INT AUTO_INCREMENT PRIMARY KEY,
    journal_entry_id INT NOT NULL,
    account_id INT NOT NULL,
    debit DECIMAL(15,2) DEFAULT 0,
    credit DECIMAL(15,2) DEFAULT 0,
    FOREIGN KEY (journal_entry_id) REFERENCES journal_entries(id) ON DELETE CASCADE,
    FOREIGN KEY (account_id) REFERENCES accounts(id)
);
