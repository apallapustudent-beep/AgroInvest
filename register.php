<?php
include "db.php";

if(isset($_POST['register'])){
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);
    $role = $_POST['role'];

    $sql = "INSERT INTO users (name, email, password, role)
            VALUES ('$name', '$email', '$password', '$role')";
    mysqli_query($conn, $sql);

    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>AgroInvest | Register</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Poppins', sans-serif;
}

body{
    height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    background:linear-gradient(135deg,#1b5e20,#4caf50);
    overflow:hidden;
}

/* Glass Card */
.form-box{
    width:360px;
    padding:40px;
    background:rgba(255,255,255,0.15);
    backdrop-filter:blur(20px);
    border-radius:20px;
    box-shadow:0 20px 40px rgba(0,0,0,0.2);
    text-align:center;
    animation:fadeIn 1.2s ease;
    border:1px solid rgba(255,255,255,0.3);
}

.form-box h2{
    color:white;
    margin-bottom:25px;
    font-weight:600;
}

/* Inputs */
.form-box input,
.form-box select{
    width:100%;
    padding:12px;
    margin-bottom:18px;
    border:none;
    border-radius:10px;
    outline:none;
    background:rgba(255,255,255,0.8);
    transition:0.3s;
    font-size:14px;
}

.form-box input:focus,
.form-box select:focus{
    transform:scale(1.05);
    box-shadow:0 0 15px rgba(255,255,255,0.6);
}

/* 3D Button */
.form-box button{
    width:100%;
    padding:12px;
    background:#1b5e20;
    color:white;
    border:none;
    border-radius:10px;
    font-size:16px;
    cursor:pointer;
    transition:0.3s;
    box-shadow:0 8px 0 #0d3b12;
}

.form-box button:hover{
    transform:translateY(-5px);
    box-shadow:0 15px 20px rgba(0,0,0,0.3);
}

.form-box button:active{
    transform:translateY(3px);
    box-shadow:0 5px 0 #0d3b12;
}

/* Login link */
.login-link{
    margin-top:18px;
    color:white;
}

.login-link a{
    color:#ffffff;
    font-weight:600;
    text-decoration:none;
}

.login-link a:hover{
    text-decoration:underline;
}

/* Animation */
@keyframes fadeIn{
    from{
        opacity:0;
        transform:scale(0.9);
    }
    to{
        opacity:1;
        transform:scale(1);
    }
}
</style>
</head>
<body>

<div class="left">
    <div class="left-text">
        <h1>AgroInvest 🌾</h1>
        <p>Invest in crops. Grow the future.</p>
    </div>
</div>

<div class="right">
    <div class="form-box">
        <h2>Create Account</h2>

        <form method="POST">
            <input type="text" name="name" placeholder="Full Name" required>
            <input type="email" name="email" placeholder="Email Address" required>
            <input type="password" name="password" placeholder="Password" required>

            <select name="role">
                <option value="farmer">Farmer</option>
                <option value="investor">Investor</option>
            </select>

            <button type="submit" name="register">Register</button>
        </form>

        <div class="login-link">
            Already have an account? <a href="login.php">Login</a>
        </div>
    </div>
</div>

</body>
</html>