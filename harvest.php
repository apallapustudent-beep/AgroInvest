<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

$crop_id = intval($_POST['crop_id']);

/* Get Crop ROI */
$cropQuery = mysqli_query($conn,
    "SELECT roi_percentage FROM crops WHERE id = $crop_id");

$crop = mysqli_fetch_assoc($cropQuery);

if (!$crop) {
    die("Crop not found.");
}

$roi = $crop['roi_percentage'];
$commission_rate = 5; // 5% commission

/* Get Active Investments */
$investments = mysqli_query($conn,
    "SELECT * FROM investments WHERE crop_id=$crop_id AND status='Active'");

while($inv = mysqli_fetch_assoc($investments)) {

    $investor_id = $inv['investor_id'];
    $amount = $inv['amount'];

    /* Calculate Profit */
    $profit = ($amount * $roi) / 100;

    /* Calculate Commission */
    $commission = ($profit * $commission_rate) / 100;

    /* Investor gets profit minus commission */
    $final_profit = $profit - $commission;
    $totalReturn = $amount + $final_profit;

    /* Add money back to wallet */
    mysqli_query($conn,
        "UPDATE users
         SET wallet_balance = wallet_balance + $totalReturn
         WHERE id = $investor_id");

    /* Store Commission */
    mysqli_query($conn,
        "INSERT INTO platform_earnings (crop_id, investor_id, commission_amount)
         VALUES ($crop_id, $investor_id, $commission)");

    /* Store Transaction */
    mysqli_query($conn,
        "INSERT INTO transactions (user_id, type, amount, reference_id)
         VALUES ($investor_id, 'profit', $totalReturn, $crop_id)");

    /* Mark investment completed */
    mysqli_query($conn,
        "UPDATE investments
         SET status='Completed'
         WHERE id = ".$inv['id']);
}

header("Location: admin_dashboard.php");
exit();
?>