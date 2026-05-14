<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$investor_id = $_SESSION['user_id'];

$sql = "SELECT investments.*, crops.crop_name, crops.roi_percentage, 
        users.name AS farmer_name
        FROM investments
        JOIN crops ON investments.crop_id = crops.id
        JOIN users ON crops.farmer_id = users.id
        WHERE investments.investor_id = $investor_id";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
<title>My Investments</title>

<style>

body{
font-family: Arial;
background:#f4f6f9;
margin:0;
}

.header{
background:#2e7d32;
color:white;
padding:18px 30px;
display:flex;
justify-content:space-between;
}

.header a{
color:white;
text-decoration:none;
font-weight:bold;
}

.container{
max-width:1000px;
margin:auto;
padding:30px;
}

.card{
background:white;
padding:20px;
margin-bottom:20px;
border-radius:12px;
box-shadow:0 6px 15px rgba(0,0,0,0.1);
}

.crop-title{
font-size:20px;
font-weight:bold;
margin-bottom:10px;
}

.info{
margin:6px 0;
color:#444;
}

.roi{
background:#e8f5e9;
color:#2e7d32;
padding:5px 12px;
border-radius:20px;
font-weight:bold;
display:inline-block;
margin-bottom:10px;
}

.profit{
color:#2e7d32;
font-weight:bold;
}

.return{
font-weight:bold;
}

</style>
</head>

<body>

<div class="header">

<div>My Investments</div>

<div>
<a href="investor_dashboard.php">⬅ Back</a>
</div>

</div>


<div class="container">

<?php

if ($result->num_rows > 0) {

while($row = $result->fetch_assoc()) {

$amount = $row['amount'];

$roi = $row['roi_percentage'];

$profit = ($amount * $roi) / 100;

$total = $amount + $profit;

?>

<div class="card">

<div class="crop-title">
<?php echo $row['crop_name']; ?>
</div>

<div class="roi">
ROI <?php echo $roi; ?>%
</div>

<p class="info"><strong>Farmer:</strong> <?php echo $row['farmer_name']; ?></p>

<p class="info"><strong>Amount Invested:</strong> ₹<?php echo $amount; ?></p>

<p class="info"><strong>Date:</strong> <?php echo $row['investment_date']; ?></p>

<p class="profit">Expected Profit: ₹<?php echo number_format($profit,2); ?></p>

<p class="return">Total Return After Harvest: ₹<?php echo number_format($total,2); ?></p>

</div>

<?php

}

} else {

echo "<p>No investments yet.</p>";

}

?>

</div>

</body>
</html>