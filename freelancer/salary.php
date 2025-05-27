<?php
ob_start();
include 'freelancer-navbar-template.php';
include_once 'interlinkedDB.php';
$master_con = connectToDatabase(3306);
$slave_con = connectToDatabase(3307);

// Fetch user ID from session
$userName = $_SESSION['userName'];
$stmt = $slave_con->prepare("SELECT * FROM user WHERE USER_NAME = ?");
$stmt->execute([$userName]);
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($result as $res) {
    $_SESSION['USER_ID'] = $res['USER_ID'];
}

$id = $_SESSION['USER_ID'];
$error = [];

$stmt = $slave_con->prepare("SELECT * FROM bank WHERE USER_ID = ?");
$stmt->execute([$id]);
$banks = $stmt->fetch(PDO::FETCH_ASSOC);

if (empty($banks)) {
    $error[] = "Bank Is empty. Please register first";
}
$stmt = $slave_con->prepare("SELECT * FROM payment WHERE USER_ID = ? ORDER BY PAY_DATE DESC");
$stmt->execute([$id]);
$payDetails = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $slave_con->prepare("SELECT * FROM withdraw WHERE USER_ID = ? ORDER BY WITH_DATE DESC");
$stmt->execute([$id]);
$with = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $slave_con->prepare("SELECT SUM(PAY_AMOUNT) AS total_amount FROM payment WHERE USER_ID = :user_id");
$stmt->execute([':user_id' => $id]);
$total = $stmt->fetchColumn();



//insert total
$stmt = $master_con->prepare("UPDATE bank SET BNK_AMOUNT = ? WHERE USER_ID = ?");
$stmt->execute([$total, $id]);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['withdraw_btn'])) {
    $withdrawAmount = floatval($_POST['withdraw_amount']);
    $withdrawPassword = $_POST['withdraw_password'];
    $withdrawConfirm = $_POST['withdraw_confirm'];

    if ($withdrawPassword !== $withdrawConfirm) {
        $error[] = "Password confirmation doesn't match.";
    } elseif ($withdrawAmount <= 0) {
        $error[] = "Invalid withdrawal amount.";
    } elseif ($withdrawAmount > $total) {
        $error[] = "Insufficient funds.";
    } else {
        // Deduct amount from total and update in DB
        $newTotal = $total - $withdrawAmount;

        $stmt = $master_con->prepare("UPDATE bank SET BNK_AMOUNT = ? WHERE USER_ID = ?");
        $stmt->execute([$newTotal, $id]);

        $total = $newTotal;
        $successMessage = "Withdraw successful.";
    }
}


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
                        <h2 id="totalBalanceDisplay">₱ <?=$total?></h2>
                        <p class="balance-subtitle">Available Earnings</p>
                    </div>
                </div>


                <!-- Banking Information -->
                <?php if (!empty($banks)): ?>
                    <div class="banking-info">
                        <div class="banking-card">
                            <div class="card-details">
                                <div class="card-top">
                                    <span><?= htmlspecialchars($banks['BNK_NAME']) ?></span>
                                    <i class="fas fa-wifi"></i>
                                </div>
                                <div class="card-number">
                                    <h3><?= htmlspecialchars($banks['BNK_ACC_NUM']) ?></h3>
                                </div>
                                <div class="card-bottom">
                                    <div class="card-holder">
                                        <span>CardHolder Name</span>
                                        <h4><?= htmlspecialchars($banks['BNK_USER_NAME']) ?></h4>
                                    </div>
                                    <div class="expiry-date">
                                        <span>Expired Date</span>
                                        <h4><?= htmlspecialchars($banks['BNK_EXP_DATE']) ?></h4>
                                    </div>
                                    <div class="card-logo">
                                        <img src="/api/placeholder/40/30" alt="Mastercard">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <p>No bank record found.</p>
                <?php endif; ?>

                </div>
            <div class="banking-actions">
                <button id="withdrawBtn" class="btn-withdraw">
                    <i class="fas fa-arrow-down"></i> Withdraw
                </button>

                <form action="bankDetails.php" method="post">
                    <button class="btn-details">
                        <i class="fas fa-exchange-alt"></i> Bank Details
                    </button>
                </form>

            </div>
            </div>

            <!-- Payments Table -->
        <div class="pay">
            <div class="payments-table">
                <h1>PAYMENTS</h1>
                <table>
                    <!--                    TODO: MAKE THIS RESULT FOM TRIGGERS-->
                    <thead>
                    <tr>
                        <th>AMOUNT</th>
                        <th>PAYMENT DATE</th>
                        <th>PAYMENT STATUS</th>
                        <th>PAYMENT ID</th>
                    </tr>
                    </thead>
                    <tbody id="paymentsTableBody">
                    <?php foreach ($payDetails as $pay) {?>
                        <tr>
                            <td>₱ <?=$pay['PAY_AMOUNT']?></td>
                            <td><?=$pay['PAY_DATE']?></td>
                            <td><?=$pay['PAY_STATUS']?></td>
                            <td><?=$pay['PAY_ID']?></td>
                        </tr>
                    <?php }?>

                    </tbody>
                </table>
            </div>
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
            <form method="POST" action="">
                <input type="number" name="withdraw_amount" placeholder="Amount" required>
                <input type="password" name="withdraw_password" placeholder="Pin" required>
                <input type="password" name="withdraw_confirm" placeholder="Confirm Pin" required>
                <div class="modal-actions">
                    <button type="submit" name="withdraw_btn" class="submit">Withdraw</button>
                    <button type="button" class="cancel" id="cancelWithdraw">Cancel</button>
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


    <div id="messageModal" class="modal-notif" style="display: <?= (!empty($error) || !empty($successMessage)) ? 'block' : 'none' ?>;">
        <div class="modal-content">
            <?php if (!empty($error)): ?>
                <h2 style="color: red;">Error</h2>
                <?php foreach ($error as $e): ?>
                    <p><?= htmlspecialchars($e) ?></p>
                <?php endforeach; ?>
            <?php elseif (!empty($successMessage)): ?>
                <h2 style="color: green;">Success</h2>
                <p><?= htmlspecialchars($successMessage) ?></p>
            <?php endif; ?>
            <div class="modal-actions">
                <button id="closeMessageModal">Close</button>
            </div>
        </div>
    </div>

</div>

<script>
    document.getElementById("closeMessageModal")?.addEventListener("click", function() {
        document.getElementById("messageModal").style.display = "none";
    });

    // // Global Financial State
    // const financialState = {
    //     totalBalance: 20000,
    //     totalIncome: 20000,
    //     totalWithdrawn: 0,
    //     transactions: []
    // };
    //
    // // DOM References
    // const totalBalanceDisplay = document.getElementById('totalBalanceDisplay');
    // const totalIncomeDisplay = document.getElementById('totalIncomeDisplay');
    // const totalWithdrawnDisplay = document.getElementById('totalWithdrawnDisplay');
    // const paymentsTableBody = document.getElementById('paymentsTableBody');
    // const transactionHistoryBody = document.getElementById('transactionHistoryBody');
    //
    // // Utility Functions
    // function formatCurrency(amount) {
    //     return `$${parseFloat(amount).toFixed(2)}`;
    // }
    //
    // function updateBalanceDisplays() {
    //     totalBalanceDisplay.textContent = formatCurrency(financialState.totalBalance);
    //     totalIncomeDisplay.textContent = formatCurrency(financialState.totalIncome);
    //     totalWithdrawnDisplay.textContent = formatCurrency(financialState.totalWithdrawn);
    // }
    //
    // function generateUniqueId(prefix) {
    //     return `${prefix}${Date.now().toString().slice(-6)}`;
    // }
    //
    // function getCurrentDate() {
    //     const date = new Date();
    //     return date.toLocaleDateString('en-US', {
    //         year: 'numeric',
    //         month: 'short',
    //         day: 'numeric'
    //     });
    // }
    //
    // // Transaction Management
    // function processWithdrawal(amount, details) {
    //     if (amount > financialState.totalBalance) {
    //         alert('Insufficient funds for withdrawal');
    //         return false;
    //     }
    //
    //     financialState.totalBalance -= amount;
    //     financialState.totalWithdrawn += amount;
    //
    //     // Add to transactions
    //     addTransactionToHistory('Withdrawal', amount, details, 'Success');
    //
    //     updateBalanceDisplays();
    //     return true;
    // }
    //
    // function processTransfer(amount, details) {
    //     if (amount > financialState.totalBalance) {
    //         alert('Insufficient funds for transfer');
    //         return false;
    //     }
    //
    //     financialState.totalBalance -= amount;
    //
    //     // Add to transactions
    //     addTransactionToHistory('Transfer', amount, details, 'Success');
    //
    //     updateBalanceDisplays();
    //     return true;
    // }
    //
    // function addTransactionToHistory(type, amount, description, status) {
    //     const transaction = {
    //         id: generateUniqueId('TRX'),
    //         date: getCurrentDate(),
    //         type: type,
    //         description: description,
    //         amount: amount,
    //         status: status
    //     };
    //
    //     financialState.transactions.push(transaction);
    //
    //     // Update Transaction History table if open
    //     renderTransactionHistory();
    // }
    //
    // function renderTransactionHistory() {
    //     // Clear current table
    //     transactionHistoryBody.innerHTML = '';
    //
    //     // Add all transactions, newest first
    //     financialState.transactions.slice().reverse().forEach(transaction => {
    //         const newRow = document.createElement('tr');
    //
    //         let statusClass = 'success';
    //         if (transaction.status === 'Failed') {
    //             statusClass = 'failed';
    //         } else if (transaction.status === 'Pending') {
    //             statusClass = 'pending';
    //         }
    //
    //         newRow.innerHTML = `
    //             <td>${transaction.id}</td>
    //             <td>${transaction.date}</td>
    //             <td>${transaction.type}</td>
    //             <td>${transaction.description}</td>
    //             <td>${formatCurrency(transaction.amount)}</td>
    //             <td><span class="status ${statusClass.toLowerCase()}">${transaction.status}</span></td>
    //         `;
    //
    //         transactionHistoryBody.appendChild(newRow);
    //     });
    // }
    //
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
    //
    // document.getElementById('withdrawForm').addEventListener('submit', (e) => {
    //     e.preventDefault();
    //
    //     const bankName = document.getElementById('withdrawBankName').value;
    //     const accountNumber = document.getElementById('withdrawAccountNumber').value;
    //     const phoneNumber = document.getElementById('withdrawPhoneNumber').value;
    //     const birthday = document.getElementById('withdrawBirthday').value;
    //     const password = document.getElementById('withdrawPassword').value;
    //     const amount = parseFloat(document.getElementById('withdrawAmount').value);
    //
    //     if (password !== '012090') {
    //         alert('Incorrect password');
    //         return;
    //     }
    //
    //     // Show confirmation modal
    //     const details = `
    //         <p><strong>Bank Name:</strong> ${bankName}</p>
    //         <p><strong>Account Number:</strong> ${accountNumber}</p>
    //         <p><strong>Phone Number:</strong> ${phoneNumber}</p>
    //         <p><strong>Amount:</strong> ${formatCurrency(amount)}</p>
    //     `;
    //
    //     document.getElementById('confirmationTitle').textContent = 'Confirm Withdrawal';
    //     document.getElementById('confirmationDetails').innerHTML = details;
    //
    //     // Set up confirmation action
    //     document.getElementById('confirmAction').onclick = function() {
    //         if (processWithdrawal(amount, `Withdrawal to ${bankName} (${accountNumber})`)) {
    //             alert('Withdrawal processed successfully!');
    //             document.getElementById('withdrawForm').reset();
    //             closeAllModals();
    //         }
    //     };
    //
    //     closeModal('withdrawModal');
    //     openModal('confirmationModal');
    // });
    //
    // // Transfer Flow
    // document.getElementById('transferBtn').addEventListener('click', () => {
    //     openModal('transferModal');
    // });
    //
    // document.getElementById('cancelTransfer').addEventListener('click', () => {
    //     closeModal('transferModal');
    // });
    //
    // document.getElementById('transferForm').addEventListener('submit', (e) => {
    //     e.preventDefault();
    //
    //     const bankName = document.getElementById('transferBankName').value;
    //     const accountNumber = document.getElementById('transferAccountNumber').value;
    //     const phoneNumber = document.getElementById('transferPhoneNumber').value;
    //     const password = document.getElementById('transferPassword').value;
    //     const amount = parseFloat(document.getElementById('transferAmount').value);
    //     const purpose = document.getElementById('transferPurpose').value;
    //
    //     if (password !== '012090') {
    //         alert('Incorrect password');
    //         return;
    //     }
    //
    //     // Show confirmation modal
    //     const details = `
    //         <p><strong>Bank Name:</strong> ${bankName}</p>
    //         <p><strong>Account Number:</strong> ${accountNumber}</p>
    //         <p><strong>Phone Number:</strong> ${phoneNumber}</p>
    //         <p><strong>Purpose:</strong> ${purpose}</p>
    //         <p><strong>Amount:</strong> ${formatCurrency(amount)}</p>
    //     `;
    //
    //     document.getElementById('confirmationTitle').textContent = 'Confirm Transfer';
    //     document.getElementById('confirmationDetails').innerHTML = details;
    //
    //     // Set up confirmation action
    //     document.getElementById('confirmAction').onclick = function() {
    //         if (processTransfer(amount, `Transfer to ${bankName} (${accountNumber}) - ${purpose}`)) {
    //             alert('Transfer processed successfully!');
    //             document.getElementById('transferForm').reset();
    //             closeAllModals();
    //         }
    //     };
    //
    //     closeModal('transferModal');
    //     openModal('confirmationModal');
    // });
    //
    // // Cancel Confirmation
    // document.getElementById('cancelConfirmation').addEventListener('click', () => {
    //     closeModal('confirmationModal');
    // });
    //
    // // Transaction History Modal
    // document.getElementById('transactionHistoryBtn').addEventListener('click', () => {
    //     renderTransactionHistory();
    //     openModal('transactionHistoryModal');
    // });
    //
    // document.querySelector('.close-modal').addEventListener('click', () => {
    //     closeModal('transactionHistoryModal');
    // });
    //
    // // Close modals when clicking outside
    // window.addEventListener('click', (e) => {
    //     const modals = document.querySelectorAll('.modal-overlay');
    //     modals.forEach(modal => {
    //         if (e.target === modal) {
    //             modal.style.display = 'none';
    //         }
    //     });
    // });
    //
    // // Initialize
    // document.addEventListener('DOMContentLoaded', () => {
    //     updateBalanceDisplays();
    // });
</script>
</body>
</html>