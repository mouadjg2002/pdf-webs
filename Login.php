<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = ""; // No password by default
$dbname = "register"; // Replace with your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variable to store the last inserted record
$last_inserted_record = "";

// Check if the register form is submitted
if (isset($_POST["register_submit"])) {
    $name = $_POST["username"];
    $email = $_POST["email"];
    $password = $_POST["password"];

    // Hash the password for security
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Prepare and bind SQL statement
    $stmt = $conn->prepare("INSERT INTO register (username, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $email, $hashed_password);

    // Execute the statement
    if ($stmt->execute() === TRUE) {
        $last_inserted_record = "New record created successfully";
    } else {
        echo "Error: " . $stmt->error;
    }

    // Close statement
    $stmt->close();
}

// Check if the login form is submitted
if (isset($_POST["login_submit"])) {
    $email = $_POST["email"];
    $password = $_POST["password"];

    // Prepare SQL statement to retrieve user with the provided email
    $stmt = $conn->prepare("SELECT * FROM register WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            // Password is correct, set session variables and redirect to upload.php
            session_start();
            $_SESSION['loggedin'] = true;
            $_SESSION['username'] = $user['username'];
            header('Location: upload.php');
            exit;
        } else {
            // Password is incorrect
            echo "Invalid password";
        }
    } else {
        // User with provided email not found
        echo "User not found";
    }

    // Close statement
    $stmt->close();
}

// Close connection
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <style>
        body, html {
            height: 100%;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #818589; /* Change background color */
        }

        /* Your CSS styles here */

        .wrapper {
            --input-focus: #2d8cf0;
            --font-color: #323232;
            --font-color-sub: #666;
            --bg-color: #fff;
            --bg-color-alt: #666;
            --main-color: #323232;
        }

        /* switch card */
        .switch {
            transform: translateY(-200px);
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            gap: 30px;
            width: 50px;
            height: 20px;
        }

        .card-side::before {
            position: absolute;
            content: 'Log in';
            left: -70px;
            top: 0;
            width: 100px;
            text-decoration: underline;
            color: var(--font-color);
            font-weight: 600;
        }

        .card-side::after {
            position: absolute;
            content: 'Sign up';
            left: 70px;
            top: 0;
            width: 100px;
            text-decoration: none;
            color: var(--font-color);
            font-weight: 600;
        }

        .toggle {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            box-sizing: border-box;
            border-radius: 5px;
            border: 2px solid var(--main-color);
            box-shadow: 4px 4px var(--main-color);
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: var(--bg-colorcolor);
            transition: 0.3s;
        }

        .slider:before {
            box-sizing: border-box;
            position: absolute;
            content: "";
            height: 20px;
            width: 20px;
            border: 2px solid var(--main-color);
            border-radius: 5px;
            left: -2px;
            bottom: 2px;
            background-color: var(--bg-color);
            box-shadow: 0 3px 0 var(--main-color);
            transition: 0.3s;
        }

        .toggle:checked + .slider {
            background-color: var(--input-focus);
        }

        .toggle:checked + .slider:before {
            transform: translateX(30px);
        }

        .toggle:checked ~ .card-side:before {
            text-decoration: none;
        }

        .toggle:checked ~ .card-side:after {
            text-decoration: underline;
        }

        /* card */ 

        .flip-card__inner {
            width: 300px;
            height: 350px;
            position: relative;
            background-color: transparent;
            perspective: 1000px;
            text-align: center;
            transition: transform 0.8s;
            transform-style: preserve-3d;
        }

        .toggle:checked ~ .flip-card__inner {
            transform: rotateY(180deg);
        }

        .toggle:checked ~ .flip-card__front {
            box-shadow: none;
        }

        .flip-card__front, .flip-card__back {
            padding: 20px;
            position: absolute;
            display: flex;
            flex-direction: column;
            justify-content: center;
            -webkit-backface-visibility: hidden;
            backface-visibility: hidden;
            background: lightgrey;
            gap: 20px;
            border-radius: 5px;
            border: 2px solid var(--main-color);
            box-shadow: 4px 4px var(--main-color);
        }

        .flip-card__back {
            width: 100%;
            transform: rotateY(180deg);
        }

        .flip-card__form {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
        }

        .title {
            margin: 20px 0 20px 0;
            font-size: 25px;
            font-weight: 900;
            text-align: center;
            color: var(--main-color);
        }

        .flip-card__input {
            width: 250px;
            height: 40px;
            border-radius: 5px;
            border: 2px solid var(--main-color);
            background-color: var(--bg-color);
            box-shadow: 4px 4px var(--main-color);
            font-size: 15px;
            font-weight: 600;
            color: var(--font-color);
            padding: 5px 10px;
            outline: none;
        }

        .flip-card__input::placeholder {
            color: var(--font-color-sub);
            opacity: 0.8;
        }

        .flip-card__input:focus {
            border: 2px solid var(--input-focus);
        }

        .flip-card__btn:active, .button-confirm:active {
            box-shadow: 0px 0px var(--main-color);
            transform: translate(3px, 3px);
        }

        .flip-card__btn {
            margin: 20px 0 20px 0;
            width: 120px;
            height: 40px;
            border-radius: 5px;
            border: 2px solid var(--main-color);
            background-color: var(--bg-color);
            box-shadow: 4px 4px var(--main-color);
            font-size: 17px;
            font-weight: 600;
            color: var(--font-color);
            cursor: pointer;
        } 
    
    </style>
    <title>Page Title</title>
</head>
<body>
<div class="wrapper">
    <div class="card-switch">
        <label class="switch">
            <input type="checkbox" class="toggle">
            <span class="slider"></span>
            <span class="card-side"></span>
            <div class="flip-card__inner">
                <div class="flip-card__front">
                    <div class="title">Log in</div>
                    <form class="flip-card__form" action="" method="POST"> <!-- Added method="POST" -->
                        <input class="flip-card__input" name="email" placeholder="Email" type="email">
                        <input class="flip-card__input" name="password" placeholder="Password" type="password">
                        <button class="flip-card__btn" name="login_submit">Let`s go!</button> <!-- Added name="login_submit" -->
                    </form>
                </div>
                <div class="flip-card__back">
                    <div class="title">Sign up</div>
                    <form class="flip-card__form" action="" method="POST"> <!-- Added method="POST" -->
                        <input class="flip-card__input" name="username" placeholder="Username" type="text"> <!-- Changed placeholder from "Name" to "Username" -->
                        <input class="flip-card__input" name="email" placeholder="Email" type="email">
                        <input class="flip-card__input" name="password" placeholder="Password" type="password">
                        <button class="flip-card__btn" name="register_submit">Confirm!</button> <!-- Added name="register_submit" -->
                    </form>
                </div>
            </div>
        </label>
    </div>
    <!-- Display the last inserted record -->
    
</div>
</body>
</html>
