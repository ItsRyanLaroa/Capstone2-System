<?php
include 'db_connect.php'; // Include your database connection file

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $id = isset($_POST['id']) ? $_POST['id'] : '';
    $staff_id = $_POST['staff_id'];
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $email = $_POST['email'];
    $password = isset($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : '';

    // Check if email already exists
    $check_email = $conn->query("SELECT * FROM staff_list WHERE email = '$email' AND id != '$id'");
    if($check_email->num_rows > 0){
        echo 2; // Email already exists
        exit;
    }

    // Check if staff ID already exists
    $check_staff_id = $conn->query("SELECT * FROM staff_list WHERE staff_id = '$staff_id' AND id != '$id'");
    if($check_staff_id->num_rows > 0){
        echo 3; // Staff ID already exists
        exit;
    }

    // Upload avatar image if present
    $avatar = '';
    if(isset($_FILES['img']) && $_FILES['img']['tmp_name'] != ''){
        $avatar = 'staff-'.time().'.'.pathinfo($_FILES['img']['name'], PATHINFO_EXTENSION);
        move_uploaded_file($_FILES['img']['tmp_name'], 'assets/uploads/'.$avatar);
    }

    if(empty($id)){
        // Insert new staff record
        $stmt = $conn->prepare("INSERT INTO staff_list (staff_id, firstname, lastname, email, password, avatar) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $staff_id, $firstname, $lastname, $email, $password, $avatar);
    } else {
        // Update existing staff record
        if(!empty($password)){
            $stmt = $conn->prepare("UPDATE staff_list SET staff_id = ?, firstname = ?, lastname = ?, email = ?, password = ?, avatar = ? WHERE id = ?");
            $stmt->bind_param("ssssssi", $staff_id, $firstname, $lastname, $email, $password, $avatar, $id);
        } else {
            $stmt = $conn->prepare("UPDATE staff_list SET staff_id = ?, firstname = ?, lastname = ?, email = ?, avatar = ? WHERE id = ?");
            $stmt->bind_param("sssssi", $staff_id, $firstname, $lastname, $email, $avatar, $id);
        }
    }

    if($stmt->execute()){
        echo 1; // Success
    } else {
        echo $conn->error; // Return any SQL error
    }

    $stmt->close();
    $conn->close();
}
?>
