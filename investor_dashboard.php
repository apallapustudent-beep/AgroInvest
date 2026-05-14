<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'investor') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$walletQuery = "SELECT wallet_balance FROM users WHERE id = $user_id";
$walletResult = mysqli_query($conn,$walletQuery);
$walletData = mysqli_fetch_assoc($walletResult);
$wallet = $walletData['wallet_balance'];
?>

<!DOCTYPE html>
<html>
<head>
<title>Investor Dashboard</title>

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
align-items:center;
font-size:18px;
}

.header a{
color:white;
text-decoration:none;
font-weight:bold;
}

.container{
padding:30px;
max-width:1300px;
margin:auto;
display:grid;
grid-template-columns:repeat(auto-fit,minmax(360px,1fr));
gap:25px;
}

.page-title{
grid-column:1/-1;
font-size:26px;
font-weight:bold;
margin-bottom:5px;
}

.card{
background:white;
border-radius:14px;
box-shadow:0 10px 25px rgba(0,0,0,0.08);
overflow:hidden;
transition:0.3s;
}

.card:hover{
transform:translateY(-4px);
box-shadow:0 14px 30px rgba(0,0,0,0.15);
}

.card img{
width:100%;
height:200px;
object-fit:cover;
}

.card-content{
padding:20px;
}

.crop-header{
display:flex;
justify-content:space-between;
align-items:center;
margin-bottom:10px;
}

.crop-title{
font-size:20px;
font-weight:bold;
}

.roi{
background:#e8f5e9;
color:#2e7d32;
padding:6px 12px;
border-radius:20px;
font-weight:bold;
font-size:14px;
}

.info{
margin:6px 0;
font-size:15px;
color:#444;
}

.progress-bar{
background:#e0e0e0;
height:12px;
border-radius:8px;
overflow:hidden;
margin-top:10px;
}

.progress{
background:linear-gradient(90deg,#66bb6a,#2e7d32);
height:100%;
}

.funding-text{
font-size:13px;
color:#555;
margin-top:4px;
}

.invest-box{
background:#f7faf7;
padding:15px;
margin-top:15px;
border-radius:10px;
border:1px solid #e5efe5;
}

input{
padding:10px;
width:160px;
border:1px solid #ccc;
border-radius:6px;
font-size:14px;
}

button{
padding:10px 22px;
background:#2e7d32;
color:white;
border:none;
border-radius:6px;
cursor:pointer;
font-weight:bold;
margin-top:8px;
}

button:hover{
background:#1b5e20;
}

.profit{
color:#2e7d32;
font-weight:bold;
margin-top:5px;
font-size:14px;
}

.return{
color:#333;
font-weight:bold;
font-size:14px;
}

.funded{
color:green;
font-weight:bold;
margin-top:10px;
font-size:16px;
}

</style>
</head>

<body>

<div class="header">

<div>
🌾 AgroInvest Investor Panel
</div>

<div>
Welcome, <?php echo $_SESSION['name']; ?> |
Wallet: ₹<?php echo $wallet; ?> |
<a href="logout.php">Logout</a>
</div>

</div>

<div class="container">

<div class="page-title">Available Crops for Investment</div>

<?php

$query = "SELECT crops.*, users.name AS farmer_name
FROM crops
JOIN users ON crops.farmer_id = users.id";

$result = mysqli_query($conn,$query);

while($row = mysqli_fetch_assoc($result)){

$crop_id = $row['id'];

$sumQuery = "SELECT SUM(amount) as total FROM investments WHERE crop_id = $crop_id";
$sumResult = mysqli_query($conn,$sumQuery);
$sumData = mysqli_fetch_assoc($sumResult);

$totalInvested = $sumData['total'] ? $sumData['total'] : 0;

$requiredAmount = $row['investment_required'];

$remainingAmount = $requiredAmount - $totalInvested;

$progressPercent = ($requiredAmount > 0) ?
min(100, ($totalInvested / $requiredAmount) * 100) : 0;

?>

<div class="card">

<?php if(!empty($row['image'])): ?>
<img src="uploads/<?php echo $row['image']; ?>">
<?php endif; ?>

<div class="card-content">

<div class="crop-header">

<div class="crop-title">
<?php echo $row['crop_name']; ?>
</div>

<div class="roi">
ROI <?php echo $row['roi_percentage']; ?>%
</div>

</div>

<p class="info"><strong>Farmer:</strong> <?php echo $row['farmer_name']; ?></p>

<p class="info"><strong>Investment Required:</strong> ₹<?php echo $requiredAmount; ?></p>

<p class="info"><strong>Description:</strong> <?php echo $row['description']; ?></p>

<p class="info"><strong>Total Invested:</strong> ₹<?php echo $totalInvested; ?></p>

<p class="info"><strong>Remaining:</strong> ₹<?php echo $remainingAmount; ?></p>

<div class="progress-bar">
<div class="progress" style="width: <?php echo $progressPercent; ?>%;"></div>
</div>

<div class="funding-text">
<?php echo round($progressPercent); ?>% Funded
</div>

<?php if($remainingAmount > 0): ?>

<div class="invest-box">

<form action="invest.php" method="POST">

<input type="hidden" name="crop_id" value="<?php echo $crop_id; ?>">

<input type="number"
name="amount"
id="amount_<?php echo $crop_id; ?>"
min="1"
placeholder="Enter Amount"
oninput="calculateProfit(<?php echo $row['roi_percentage']; ?>,<?php echo $crop_id; ?>)"
required>

<p id="profit_<?php echo $crop_id; ?>" class="profit"></p>

<p id="return_<?php echo $crop_id; ?>" class="return"></p>

<button type="submit">Invest</button>

</form>

</div>

<?php else: ?>

<p class="funded">✅ Fully Funded</p>

<?php endif; ?>

</div>
</div>

<?php } ?>

</div>

<script>

function calculateProfit(roi,cropId){

let amount = document.getElementById("amount_"+cropId).value;

if(amount === ""){
document.getElementById("profit_"+cropId).innerHTML = "";
document.getElementById("return_"+cropId).innerHTML = "";
return;
}

let profit = (amount * roi) / 100;
let total = parseFloat(amount) + profit;

document.getElementById("profit_"+cropId).innerHTML =
"Expected Profit: ₹" + profit.toFixed(2);

document.getElementById("return_"+cropId).innerHTML =
"Total Return After Harvest: ₹" + total.toFixed(2);

}

</script>

</body>
</html>