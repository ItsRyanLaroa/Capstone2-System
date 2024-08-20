<?php
// Connect to the database
$conn = new mysqli('localhost', 'root', '', 'evaluation_db');

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize error message variables
$id_error = $email_error = $password_error = $confirm_password_error = $avatar_error = "";

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Retrieve and sanitize input fields
    $firstname = htmlspecialchars(trim($_POST['firstname']));
    $lastname = htmlspecialchars(trim($_POST['lastname']));
    $identifier = htmlspecialchars(trim($_POST['identifier']));  // Update this field name if necessary
    $email = htmlspecialchars(trim($_POST['email']));
    $password = htmlspecialchars(trim($_POST['password']));
    $confirm_password = htmlspecialchars(trim($_POST['confirm_password']));

    // Validate input fields (e.g., email format, password length)
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $email_error = "Invalid email format.";
    }
    if (strlen($password) < 6) {
        $password_error = "Password must be at least 6 characters.";
    }
    if ($password !== $confirm_password) {
        $confirm_password_error = "Passwords do not match.";
    }

    // Process the avatar upload
    $uploadDir = 'uploads/avatars/';
    // Check if the directory exists, if not, create it
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $avatarName = 'avatar_' . uniqid() . '.' . pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
    $avatarPath = $uploadDir . $avatarName;

    if (move_uploaded_file($_FILES['avatar']['tmp_name'], $avatarPath)) {
        // File successfully uploaded
    } else {
        // Handle the error
        $avatar_error = 'Failed to upload the avatar. Please try again.';
    }

    // If there are no errors, proceed with inserting the data into the database
    if (empty($id_error) && empty($email_error) && empty($password_error) && empty($confirm_password_error) && empty($avatar_error)) {
        // Hash the password using MD5
        $hashed_password = md5($password);

        // Prepare and bind the SQL statement
        $stmt = $conn->prepare("INSERT INTO student_list (firstname, lastname, school_id, email, password, avatar) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $firstname, $lastname, $identifier, $email, $hashed_password, $avatarPath);

        // Execute the query
        if ($stmt->execute()) {
            // Redirect to a success page or display a success message
            echo "Registration successful!";
        } else {
            echo "Error: " . $stmt->error;
        }

        // Close the statement
        $stmt->close();
    }
}

// Close the database connection
$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration</title>
    <link rel="stylesheet" href="Css/registration.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" />
    <style>
        .invalid-input {
            border: 1px solid red;
        }
        .error-message {
            color: red;
            font-size: 0.8em;
        }
        .title {
            position: relative;
            background-color: rgba(189, 169, 169, 0.5);
            height: 10vh;
            padding: 10px;
            overflow: hidden;
            color: yellow;
            border-radius: 40px 0px 40px 0;
        }
        .title a {
            color: yellow;
        }
        body {
            background: #e3f2fd;
        }
        button {
            font-size: 18px;
            font-weight: 400;
            color: #fff;
            padding: 14px 22px;
            border: none;
            background: #4070f4;
            border-radius: 6px;
            cursor: pointer;
        }
        button:hover {
            background-color: #265df2;
        }
        button.show-modal,
        .modal-box {
            position: fixed;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            box-shadow: 0 5px 10px rgba(0, 0, 0, 0.1);
        }
        .overlay {
            position: fixed;
            height: 100%;
            width: 100%;
            background: rgba(0, 0, 0, 0.3);
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease;
        }
        .overlay.active {
            opacity: 1;
            pointer-events: auto;
        }
        .modal-box {
            display: flex;
            flex-direction: column;
            align-items: center;
            max-width: 380px;
            width: 100%;
            padding: 30px 20px;
            border-radius: 24px;
            background-color: #fff;
            opacity: 0;
            pointer-events: none;
            transition: all 0.3s ease;
            transform: translate(-50%, -50%) scale(1.2);
        }
        .modal-box.active {
            opacity: 1;
            pointer-events: auto;
            transform: translate(-50%, -50%) scale(1);
        }
        .modal-box i {
            font-size: 70px;
            color: #75d479;
        }
        .modal-box h2 {
            margin-top: 20px;
            font-size: 25px;
            font-weight: 500;
            color: #333;
        }
        .modal-box h3 {
            font-size: 16px;
            font-weight: 400;
            color: #333;
            text-align: center;
        }
        .modal-box .buttons {
            margin-top: 25px;
        }
        .modal-box button {
            font-size: 14px;
            padding: 6px 12px;
            margin: 0 10px;
        }
        .regContainer {
        display: flex;
        width: 820px;
        border: 1px solid black;
        padding: 25px 30px;
        margin: auto;
        color: black;
        /* background-color: #b31b1b; */
        z-index: 2;
        background-color: rgba(255, 255, 255, 0.8);
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        width: 750px;
        }
        .form .button input {
        height: 100%;
        width: 100%;
        outline: none;
        font-weight: 500;
        letter-spacing: 1px;
        color: #fff;
        background-color:  #b31b1b;;
        }

        .form .button input:hover {
        background-color: #ff4242;
        }
        a {
        color: blue;
        text-decoration: none;
        font-size: 13px;
        }

    </style>
</head>
<body>
<main>  
      <!-- <header>
            <nav class="nav container">
                <div class="title">
                    <h2 class="nav_logo"><a href="#">Faculty Evaluation System</a></h2>
                </div>
                <ul class="menu_items">
                    <li><a href="index.php" class="nav_link">Login</a></li>
                    <li><a href="register.php" class="nav_link">Register</a></li>
                </ul>
                <img src="images/bars.svg" alt="timesicon" id="menu_toggle" />
            </nav>
        </header> -->
        <div class="regContainer">
            <form action="register.php" method="post" enctype="multipart/form-data" class="form">
                <h2 style="color: red;">Registration</h2>
                <div class="user-details">
                    <div class="input-field">
                        <span class="details">First Name</span>
                        <input type="text" name="firstname" placeholder="Enter your first name" required />
                    </div>
                    <div class="input-field">
                        <span class="details">Last Name</span>
                        <input type="text" name="lastname" placeholder="Enter your last name" required />
                    </div>
                    <div class="input-field">
                        <span class="details">School Id</span>
                        <input type="text" name="identifier" placeholder="School ID" required />
                    </div>
                    <div class="input-field">
                        <span class="details">Email</span>
                        <input type="email" name="email" placeholder="Enter your email" required />
                    </div>
                    <div class="input-field">
                        <span class="details">Password</span>
                        <input type="password" name="password" placeholder="Enter your password" required />
                    </div>
                    <div class="input-field">
                        <span class="details">Confirm Password</span>
                        <input type="password" name="confirm_password" placeholder="Confirm your password" required />
                    </div>
                    <div class="input-field">
                        <span class="details">Avatar</span>
                        <input type="file" name="avatar" accept="image/*" required />
                    </div>
                </div>
                <div class="button">
                    <input type="submit" value="Register" />
                </div>
                <p style="text-align:center;"><a href="index.php">Already have account? Login here...</a></p>
            </form>
        </div>
    </main>
    <script>
        const overlay = document.querySelector(".overlay"),
            closeBtn = document.querySelector(".close-btn"),
            modalBox = document.querySelector(".modal-box");

        if (modalBox) {
            overlay.addEventListener("click", () => {
                overlay.classList.remove("active");
                modalBox.classList.remove("active");
            });
            closeBtn.addEventListener("click", () => {
                overlay.classList.remove("active");
                modalBox.classList.remove("active");
            });
        }
    </script>
</body>
</html>

