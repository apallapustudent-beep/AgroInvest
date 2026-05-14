<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

/* Dashboard Stats */
$totalCrops = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) as total FROM crops"))['total'];

$totalInvestments = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT SUM(amount) as total FROM investments"))['total'];

$totalInvestors = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) as total FROM users WHERE role='investor'"))['total'];

$totalWallet = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT SUM(wallet_balance) as total FROM users"))['total'];
?>

<!DOCTYPE html>
<html>
<head>
<title>Admin Dashboard</title>
<style>
body { font-family: Arial; background:#f4f6f9; margin:0; }
.header {
    background:#1b5e20;
    color:white;
    padding:15px 30px;
    display:flex;
    justify-content:space-between;
}
.container { padding:30px; }

.cards { display:flex; gap:20px; margin-bottom:30px; }
.card {
    flex:1;
    background:white;
    padding:20px;
    border-radius:8px;
    box-shadow:0 2px 10px rgba(0,0,0,0.1);
    text-align:center;
}

.crop-card {
    background:white;
    padding:20px;
    margin-bottom:20px;
    border-radius:8px;
    box-shadow:0 2px 10px rgba(0,0,0,0.1);
}
button {
    padding:8px 15px;
    background:#2e7d32;
    color:white;
    border:none;
    border-radius:4px;
    cursor:pointer;
}
button:hover { background:#1b5e20; }
</style>
</head>
<body>

<div class="header">
    <h2>Admin Dashboard 👨‍💼</h2>
    <a href="logout.php" style="color:white;">Logout</a>
</div>

<div class="container">

<div class="cards">
    <div class="card">
        <h3>Total Crops</h3>
        <p><?php echo $totalCrops; ?></p>
    </div>
    <div class="card">
        <h3>Total Investment</h3>
        <p>₹<?php echo $totalInvestments ? $totalInvestments : 0; ?></p>
    </div>
    <div class="card">
        <h3>Total Investors</h3>
        <p><?php echo $totalInvestors; ?></p>
    </div>
    <div class="card">
        <h3>Total Wallet Balance</h3>
        <p>₹<?php echo $totalWallet ? $totalWallet : 0; ?></p>
    </div>
</div>

<h2>Crop Management</h2>

<?php
$crops = mysqli_query($conn, "SELECT * FROM crops");

while($crop = mysqli_fetch_assoc($crops)) {

    $crop_id = $crop['id'];

    $sumQuery = mysqli_query($conn,
        "SELECT SUM(amount) as total FROM investments WHERE crop_id=$crop_id");

    $sumData = mysqli_fetch_assoc($sumQuery);
    $totalInvested = $sumData['total'] ? $sumData['total'] : 0;

    $required = $crop['investment_required'];
    $progress = $required > 0 ? round(($totalInvested/$required)*100) : 0;
?>

<div class="crop-card">
    <p><strong>Crop:</strong> <?php echo $crop['crop_name']; ?></p>
    <p><strong>Required:</strong> ₹<?php echo $required; ?></p>
    <p><strong>Total Invested:</strong> ₹<?php echo $totalInvested; ?></p>
    <p><strong>Progress:</strong> <?php echo $progress; ?>%</p>

    <form action="harvest.php" method="POST">
        <input type="hidden" name="crop_id" value="<?php echo $crop_id; ?>">
        <button type="submit">Mark as Harvested & Distribute Profit</button>
    </form>
</div>

<?php } ?>

</div>
</body>
</html>