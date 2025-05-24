<?php
include_once 'SecurityCheck.php';
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="icon" type="image/x-icon" href="../imgs/inlFavicon@4x.png">
    <link rel="stylesheet" href="salary-style.css">
    <title>Earnings</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"/>
</head>
<body>

<?php include 'freelancer-navbar-template.php' ?>

<div class="container">
    <!-- Main Content -->
    <div class="main-content">
        <!-- Header with Actions -->
        <div class="earnings-header">
            <header>
            </header>
        </div>

        <!-- Dashboard Content -->
        <div class="dashboard-content">
            <div class="cards-container">
                <!-- Balance Card -->
                <div class="balance-card">
                    <div class="balance-info">
                        <p>Total Balance</p>
                        <h2 id="totalBalanceDisplay">₱ 20000.00</h2>
                        <p class="balance-subtitle">Available Earnings</p>
                    </div>
                </div>

                <!-- Income/Outcome Cards -->
                <div class="income-outcome">
                    <div class="income">
                        <i class="fas fa-arrow-down"></i>
                        <div>
                            <span>Total Income</span>
                            <h3 id="totalIncomeDisplay">₱ 20,000.00</h3>
                        </div>
                    </div>
                    <div class="outcome">
                        <i class="fas fa-arrow-up"></i>
                        <div>
                            <span>Total Withdrawn</span>
                            <h3 id="totalWithdrawnDisplay">₱ 0.00</h3>
                        </div>
                    </div>
                </div>

                <!-- Banking Information -->
                <div class="banking-info">
                    <div class="banking-card">
                        <div class="card-details">
                            <div class="card-top">
                                <span>ADRBank</span>
                                <i class="fas fa-wifi"></i>
                            </div>
                            <div class="card-number">
                                <h3>8763 2736 9873 0329</h3>
                            </div>
                            <div class="card-bottom">
                                <div class="card-holder">
                                    <span>CardHolder Name</span>
                                    <h4>HILLERY NEVELIN</h4>
                                </div>
                                <div class="expiry-date">
                                    <span>Expired Date</span>
                                    <h4>10/28</h4>
                                </div>
                                <div class="card-logo">
                                    <img src="/api/placeholder/40/30" alt="Mastercard">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Withdraw and Transfer Buttons -->
                    <div class="banking-actions">
                        <button id="withdrawBtn" class="banking-action-btn">
                            <i class="fas fa-arrow-down"></i> Withdraw
                        </button>
                        <button id="transferBtn" class="banking-action-btn">
                            <i class="fas fa-exchange-alt"></i> Bank Details
                        </button>
                    </div>
                </div>
            </div>

            <!-- Payments Table -->
            <div class="payments-table">
                <table>
                    <thead>
                    <tr>
                        <th>PROJECT NAME</th>
                        <th>AMOUNT</th>
                        <th>PAYMENT DATE</th>
                        <th>PAYMENT STATUS</th>
                    </tr>
                    </thead>
                    <tbody id="paymentsTableBody">
                    <tr>
                        <td>Cafe Logo</td>
                        <td>$299.00</td>
                        <td>16-05-2025</td>
                        <td><span class="status success">Success</span></td>
                    </tr>
                    <tr>
                        <td>Interior Design</td>
                        <td>$299.00</td>
                        <td>16-05-2025</td>
                        <td><span class="status failed">Failed</span></td>
                    </tr>
                    <tr>
                        <td>Character Illustration</td>
                        <td>$299.00</td>
                        <td>16-05-2025</td>
                        <td><span class="status pending">Pending</span></td>
                    </tr>
                    <tr>
                        <td>Custom Shirt Design</td>
                        <td>$299.00</td>
                        <td>16-05-2025</td>
                        <td><span class="status success">Success</span></td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <!-- Transaction History Button -->
<!--            <div class="transaction-history-section">-->
<!--                <button id="transactionHistoryBtn" class="transaction-history-btn">-->
<!--                    <i class="fas fa-history"></i> Transaction History-->
<!--                </button>-->
<!--            </div>-->
        </div>
    </div>

    <!-- Withdrawal Modal -->
    <div id="withdrawModal" class="modal-overlay">
        <div class="fund-modal">
            <h2>Withdraw</h2>
            <form id="withdrawForm">
                <input type="number" id="withdrawAmount" placeholder="Amount" required>
                <input type="password" id="withdrawPassword" placeholder="Password" required>
                <input type="password" id="withdrawPassword" placeholder="Confirm Password" required>
                <div class="modal-actions">
                    <button type="button" class="cancel" id="cancelWithdraw">Cancel</button>
                    <button type="submit" class="submit">Withdraw</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Transfer Modal -->
    <div id="transferModal" class="modal-overlay">
        <div class="fund-modal">
            <h2>Bank Details</h2>
            <form id="transferForm">
                <input type="text" id="transferBankName" placeholder="Account Name" required>
                <input type="text" id="transferAccountNumber" placeholder="Bank Account Number" required>
                <input type="tel" id="transferPhoneNumber" placeholder="Phone Number" required>
                <input type="password" id="transferPassword" placeholder="Password" required>
                <input type="password" id="transferPassword" placeholder="Confirm Password" required>
                <div class="modal-actions">
                    <button type="button" class="cancel" id="cancelTransfer">Cancel</button>
                    <button type="submit" class="submit">Save</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div id="confirmationModal" class="modal-overlay">
        <div class="fund-modal">
            <h2 id="confirmationTitle">Confirm Action</h2>
            <div id="confirmationDetails" class="confirmation-details">
                <!-- Details will be inserted here -->
            </div>
            <div class="modal-actions">
                <button type="button" class="cancel" id="cancelConfirmation">No, Cancel</button>
                <button type="button" class="submit" id="confirmAction">Yes, Proceed</button>
            </div>
        </div>
    </div>

    <!-- Transaction History Modal -->
<!--    <div id="transactionHistoryModal" class="transaction-history-modal">-->
<!--        <div class="transaction-history-modal-content">-->
<!--            <span class="close-modal">&times;</span>-->
<!--            <h2>Transaction History</h2>-->
<!--            <table class="transaction-history-table">-->
<!--                <thead>-->
<!--                <tr>-->
<!--                    <th>Transaction ID</th>-->
<!--                    <th>Date</th>-->
<!--                    <th>Type</th>-->
<!--                    <th>Description</th>-->
<!--                    <th>Amount</th>-->
<!--                    <th>Status</th>-->
<!--                </tr>-->
<!--                </thead>-->
<!--                <tbody id="transactionHistoryBody">-->
<!--                <!-- Transactions will be dynamically added here -->-->
<!--                </tbody>-->
<!--            </table>-->
<!--        </div>-->
<!--    </div>-->
</div>

<script>
    // Financial Management System

    // Global Financial State
    const financialState = {
        totalBalance: 20000,
        totalIncome: 20000,
        totalWithdrawn: 0,
        transactions: []
    };

    // DOM References
    const totalBalanceDisplay = document.getElementById('totalBalanceDisplay');
    const totalIncomeDisplay = document.getElementById('totalIncomeDisplay');
    const totalWithdrawnDisplay = document.getElementById('totalWithdrawnDisplay');
    const paymentsTableBody = document.getElementById('paymentsTableBody');
    const transactionHistoryBody = document.getElementById('transactionHistoryBody');

    // Utility Functions
    function formatCurrency(amount) {
        return `$${parseFloat(amount).toFixed(2)}`;
    }

    function updateBalanceDisplays() {
        totalBalanceDisplay.textContent = formatCurrency(financialState.totalBalance);
        totalIncomeDisplay.textContent = formatCurrency(financialState.totalIncome);
        totalWithdrawnDisplay.textContent = formatCurrency(financialState.totalWithdrawn);
    }

    function generateUniqueId(prefix) {
        return `${prefix}${Date.now().toString().slice(-6)}`;
    }

    function getCurrentDate() {
        const date = new Date();
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    }

    // Transaction Management
    function processWithdrawal(amount, details) {
        if (amount > financialState.totalBalance) {
            alert('Insufficient funds for withdrawal');
            return false;
        }

        financialState.totalBalance -= amount;
        financialState.totalWithdrawn += amount;

        // Add to transactions
        addTransactionToHistory('Withdrawal', amount, details, 'Success');

        updateBalanceDisplays();
        return true;
    }

    function processTransfer(amount, details) {
        if (amount > financialState.totalBalance) {
            alert('Insufficient funds for transfer');
            return false;
        }

        financialState.totalBalance -= amount;

        // Add to transactions
        addTransactionToHistory('Transfer', amount, details, 'Success');

        updateBalanceDisplays();
        return true;
    }

    function addTransactionToHistory(type, amount, description, status) {
        const transaction = {
            id: generateUniqueId('TRX'),
            date: getCurrentDate(),
            type: type,
            description: description,
            amount: amount,
            status: status
        };

        financialState.transactions.push(transaction);

        // Update Transaction History table if open
        renderTransactionHistory();
    }

    function renderTransactionHistory() {
        // Clear current table
        transactionHistoryBody.innerHTML = '';

        // Add all transactions, newest first
        financialState.transactions.slice().reverse().forEach(transaction => {
            const newRow = document.createElement('tr');

            let statusClass = 'success';
            if (transaction.status === 'Failed') {
                statusClass = 'failed';
            } else if (transaction.status === 'Pending') {
                statusClass = 'pending';
            }

            newRow.innerHTML = `
                <td>${transaction.id}</td>
                <td>${transaction.date}</td>
                <td>${transaction.type}</td>
                <td>${transaction.description}</td>
                <td>${formatCurrency(transaction.amount)}</td>
                <td><span class="status ${statusClass.toLowerCase()}">${transaction.status}</span></td>
            `;

            transactionHistoryBody.appendChild(newRow);
        });
    }

    // Modal Handling
    function openModal(modalId) {
        document.getElementById(modalId).style.display = 'flex';
    }

    function closeModal(modalId) {
        document.getElementById(modalId).style.display = 'none';
    }

    function closeAllModals() {
        const modals = document.querySelectorAll('.modal-overlay');
        modals.forEach(modal => modal.style.display = 'none');
    }

    // Withdraw Flow
    document.getElementById('withdrawBtn').addEventListener('click', () => {
        openModal('withdrawModal');
    });

    document.getElementById('cancelWithdraw').addEventListener('click', () => {
        closeModal('withdrawModal');
    });

    document.getElementById('withdrawForm').addEventListener('submit', (e) => {
        e.preventDefault();

        const bankName = document.getElementById('withdrawBankName').value;
        const accountNumber = document.getElementById('withdrawAccountNumber').value;
        const phoneNumber = document.getElementById('withdrawPhoneNumber').value;
        const birthday = document.getElementById('withdrawBirthday').value;
        const password = document.getElementById('withdrawPassword').value;
        const amount = parseFloat(document.getElementById('withdrawAmount').value);

        if (password !== '012090') {
            alert('Incorrect password');
            return;
        }

        // Show confirmation modal
        const details = `
            <p><strong>Bank Name:</strong> ${bankName}</p>
            <p><strong>Account Number:</strong> ${accountNumber}</p>
            <p><strong>Phone Number:</strong> ${phoneNumber}</p>
            <p><strong>Amount:</strong> ${formatCurrency(amount)}</p>
        `;

        document.getElementById('confirmationTitle').textContent = 'Confirm Withdrawal';
        document.getElementById('confirmationDetails').innerHTML = details;

        // Set up confirmation action
        document.getElementById('confirmAction').onclick = function() {
            if (processWithdrawal(amount, `Withdrawal to ${bankName} (${accountNumber})`)) {
                alert('Withdrawal processed successfully!');
                document.getElementById('withdrawForm').reset();
                closeAllModals();
            }
        };

        closeModal('withdrawModal');
        openModal('confirmationModal');
    });

    // Transfer Flow
    document.getElementById('transferBtn').addEventListener('click', () => {
        openModal('transferModal');
    });

    document.getElementById('cancelTransfer').addEventListener('click', () => {
        closeModal('transferModal');
    });

    document.getElementById('transferForm').addEventListener('submit', (e) => {
        e.preventDefault();

        const bankName = document.getElementById('transferBankName').value;
        const accountNumber = document.getElementById('transferAccountNumber').value;
        const phoneNumber = document.getElementById('transferPhoneNumber').value;
        const password = document.getElementById('transferPassword').value;
        const amount = parseFloat(document.getElementById('transferAmount').value);
        const purpose = document.getElementById('transferPurpose').value;

        if (password !== '012090') {
            alert('Incorrect password');
            return;
        }

        // Show confirmation modal
        const details = `
            <p><strong>Bank Name:</strong> ${bankName}</p>
            <p><strong>Account Number:</strong> ${accountNumber}</p>
            <p><strong>Phone Number:</strong> ${phoneNumber}</p>
            <p><strong>Purpose:</strong> ${purpose}</p>
            <p><strong>Amount:</strong> ${formatCurrency(amount)}</p>
        `;

        document.getElementById('confirmationTitle').textContent = 'Confirm Transfer';
        document.getElementById('confirmationDetails').innerHTML = details;

        // Set up confirmation action
        document.getElementById('confirmAction').onclick = function() {
            if (processTransfer(amount, `Transfer to ${bankName} (${accountNumber}) - ${purpose}`)) {
                alert('Transfer processed successfully!');
                document.getElementById('transferForm').reset();
                closeAllModals();
            }
        };

        closeModal('transferModal');
        openModal('confirmationModal');
    });

    // Cancel Confirmation
    document.getElementById('cancelConfirmation').addEventListener('click', () => {
        closeModal('confirmationModal');
    });

    // Transaction History Modal
    document.getElementById('transactionHistoryBtn').addEventListener('click', () => {
        renderTransactionHistory();
        openModal('transactionHistoryModal');
    });

    document.querySelector('.close-modal').addEventListener('click', () => {
        closeModal('transactionHistoryModal');
    });

    // Close modals when clicking outside
    window.addEventListener('click', (e) => {
        const modals = document.querySelectorAll('.modal-overlay');
        modals.forEach(modal => {
            if (e.target === modal) {
                modal.style.display = 'none';
            }
        });
    });

    // Initialize
    document.addEventListener('DOMContentLoaded', () => {
        updateBalanceDisplays();
    });
</script>
</body>
</html>