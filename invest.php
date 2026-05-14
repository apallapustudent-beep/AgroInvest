<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'investor') {
    header("Location: login.php");
    exit();
}

$investor_id = $_SESSION['user_id'];
$crop_id = intval($_POST['crop_id']);
$amount = floatval($_POST['amount']);

if ($amount <= 0) {
    die("Invalid investment amount.");
}

/* Start transaction */
$conn->begin_transaction();

try {

    /* 1. Get investor wallet balance */
    $walletStmt = $conn->prepare("SELECT wallet_balance FROM users WHERE id = ? FOR UPDATE");
    $walletStmt->bind_param("i", $investor_id);
    $walletStmt->execute();
    $walletResult = $walletStmt->get_result();
    $walletData = $walletResult->fetch_assoc();

    if (!$walletData) {
        throw new Exception("User not found.");
    }

    $currentWallet = $walletData['wallet_balance'];

    if ($amount > $currentWallet) {
        throw new Exception("Insufficient wallet balance.");
    }

    /* 2. Get crop investment required */
    $cropStmt = $conn->prepare("SELECT investment_required FROM crops WHERE id = ? FOR UPDATE");
    $cropStmt->bind_param("i", $crop_id);
    $cropStmt->execute();
    $cropResult = $cropStmt->get_result();
    $cropData = $cropResult->fetch_assoc();

    if (!$cropData) {
        throw new Exception("Crop not found.");
    }

    $requiredAmount = $cropData['investment_required'];

    /* 3. Calculate total invested */
    $totalStmt = $conn->prepare("SELECT SUM(amount) AS total FROM investments WHERE crop_id = ?");
    $totalStmt->bind_param("i", $crop_id);
    $totalStmt->execute();
    $totalResult = $totalStmt->get_result();
    $totalData = $totalResult->fetch_assoc();

    $totalInvested = $totalData['total'] ?? 0;
    $remainingAmount = $requiredAmount - $totalInvested;

    if ($remainingAmount <= 0) {
        throw new Exception("Crop already fully funded.");
    }

    if ($amount > $remainingAmount) {
        throw new Exception("Only ₹" . $remainingAmount . " remaining for this crop.");
    }

    /* 4. Insert investment */
    $insertStmt = $conn->prepare("
        INSERT INTO investments (investor_id, crop_id, amount, investment_date)
        VALUES (?, ?, ?, NOW())
    ");
    $insertStmt->bind_param("iid", $investor_id, $crop_id, $amount);
    $insertStmt->execute();

    /* 5. Deduct wallet */
    $updateWalletStmt = $conn->prepare("
        UPDATE users SET wallet_balance = wallet_balance - ? WHERE id = ?
    ");
    $updateWalletStmt->bind_param("di", $amount, $investor_id);
    $updateWalletStmt->execute();

    /* 6. Log transaction */
    $transactionStmt = $conn->prepare("
        INSERT INTO transactions (user_id, type, amount, reference_id)
        VALUES (?, 'investment', ?, ?)
    ");
    $transactionStmt->bind_param("idi", $investor_id, $amount, $crop_id);
    $transactionStmt->execute();

    /* 7. Update crop status if fully funded */
    $newTotal = $totalInvested + $amount;

    if ($newTotal >= $requiredAmount) {

        $statusStmt = $conn->prepare("UPDATE crops SET status='funded' WHERE id=?");
        $statusStmt->bind_param("i", $crop_id);
        $statusStmt->execute();
    }

    /* Commit transaction */
    $conn->commit();

    header("Location: investor_dashboard.php");
    exit();

} catch (Exception $e) {

    $conn->rollback();
    die($e->getMessage());

}
?>