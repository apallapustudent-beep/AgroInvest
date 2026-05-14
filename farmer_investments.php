<?php
session_start();
include "db.php";

if(!isset($_SESSION['user'])){
    header("Location: login.php");
}

$farmer_id = $_SESSION['user']['id'];

$sql = "SELECT crops.crop_name, users.name AS investor_name, 
        investments.amount, investments.investment_date
        FROM investments
        JOIN crops ON investments.crop_id = crops.id
        JOIN users ON investments.investor_id = users.id
        WHERE crops.farmer_id = '$farmer_id'";

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>
<head>
<title>Farmer Investments</title>
<style>
body { font-family: Arial; background:#f4f4f4; }
.header { background:green; color:white; padding:15px; }
.card {
    background:white;
    margin:20px;
    padding:20px;
    border-radius:10px;
    box-shadow:0 2px 8px rgba(0,0,0,0.1);
}
</style>
</head>
<body>

<div class="header">
<h2>Investments on Your Crops</h2>
</div>

<?php while($row = mysqli_fetch_assoc($result)){ ?>

<div class="card">
<p><b>Crop:</b> <?php echo $row['crop_name']; ?></p>
<p><b>Investor:</b> <?php echo $row['investor_name']; ?></p>
<p><b>Amount:</b> ₹<?php echo $row['amount']; ?></p>
<p><b>Date:</b> <?php echo $row['investment_date']; ?></p>
</div>

<?php } ?>

</body>
</html>