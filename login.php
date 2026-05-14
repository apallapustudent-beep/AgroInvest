<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include "db.php";

$error = "";

if (isset($_POST['login'])) {

    $email = trim($_POST['email']);
    $entered_password = trim($_POST['password']);   // IMPORTANT: trim password

    if (!empty($email) && !empty($entered_password)) {

        // Secure Prepared Statement
        $stmt = $conn->prepare("SELECT id, name, role, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {

            $user = $result->fetch_assoc();

            // Proper Password Verification
            if (password_verify($entered_password, $user['password'])) {

                session_regenerate_id(true);

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['role'] = $user['role'];

                if ($user['role'] === 'admin') {
                    header("Location: admin_dashboard.php");
                } elseif ($user['role'] === 'farmer') {
                    header("Location: farmer_dashboard.php");
                } else {
                    header("Location: investor_dashboard.php");
                }
                exit();

            } else {
                $error = "Invalid Email or Password!";
            }

        } else {
            $error = "Invalid Email or Password!";
        }

        $stmt->close();

    } else {
        $error = "Please fill all fields.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>AgroInvest | Secure Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family: Arial, sans-serif; }

        body {
            height:100vh;
            display:flex;
            justify-content:center;
            align-items:center;
            background: linear-gradient(135deg, #1b5e20, #4caf50);
        }

        .login-box {
            width:380px;
            padding:40px;
            background:#ffffff;
            border-radius:15px;
            box-shadow:0 10px 30px rgba(0,0,0,0.2);
            text-align:center;
        }

        .login-box h2 {
            margin-bottom:20px;
            color:#1b5e20;
        }

        input {
            width:100%;
            padding:12px;
            margin-bottom:15px;
            border:1px solid #ccc;
            border-radius:8px;
        }

        button {
            width:100%;
            padding:12px;
            border:none;
            border-radius:8px;
            background:#1b5e20;
            color:white;
            font-size:16px;
            cursor:pointer;
        }

        button:hover {
            background:#0d3b12;
        }

        .error {
            color:red;
            margin-bottom:15px;
            font-size:14px;
        }

        .register-link {
            margin-top:15px;
        }

        .register-link a {
            color:#1b5e20;
            font-weight:bold;
            text-decoration:none;
        }
    </style>
</head>
<body>

<div class="login-box">
    <h2>AgroInvest Login 🌾</h2>

    <?php if($error != "") { ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php } ?>

    <form method="POST">
        <input type="email" name="email" placeholder="Email Address" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" name="login">Login</button>
    </form>

    <div class="register-link">
        Don’t have an account?
        <a href="register.php">Register</a>
    </div>
</div>

</body>
</html>