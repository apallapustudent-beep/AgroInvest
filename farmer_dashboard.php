<?php
session_start();
include "db.php";

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

if(isset($_POST['submit'])){

    $farmer_id = $_SESSION['user_id'];
    $crop_name = $_POST['crop_name'];
    $investment_required = $_POST['investment_required'];
    $roi = $_POST['roi_percentage'];   // ROI added
    $description = $_POST['description'];

    // IMAGE UPLOAD
    $image_name = $_FILES['image']['name'];
    $temp_name = $_FILES['image']['tmp_name'];
    $folder = "uploads/" . $image_name;

    move_uploaded_file($temp_name, $folder);

    $sql = "INSERT INTO crops (farmer_id, crop_name, investment_required, description, image, roi_percentage)
            VALUES ('$farmer_id', '$crop_name', '$investment_required', '$description', '$image_name', '$roi')";

    mysqli_query($conn, $sql);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Farmer Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="navbar">
    AgroInvest - Farmer Dashboard
    <a href="logout.php" style="float:right; color:white;">Logout</a>
</div>

<div class="container">

    <h2>Welcome, <?php echo $_SESSION['name']; ?> 👋</h2>

    <div class="card">
        <h3>Add New Crop</h3>

        <form method="POST" enctype="multipart/form-data">
            
            Crop Name:
            <input type="text" name="crop_name" required>

            Investment Required:
            <input type="number" name="investment_required" required>

            ROI % (Investor Profit):
            <input type="number" step="0.01" name="roi_percentage" required>

            Description:
            <textarea name="description" required></textarea>

            Crop Image:
            <input type="file" name="image" required>

            <button type="submit" name="submit">Add Crop</button>
        </form>

    </div>
</div>

</body>
</html>