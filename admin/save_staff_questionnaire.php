<?php
include 'db_connect.php'; // Include your database connection

if(isset($_POST['staff_id']) && isset($_POST['questions'])) {
    $staff_id = $_POST['staff_id'];
    $questions = $_POST['questions']; // Assume this is a JSON-encoded array of questions

    // Delete existing questions for the staff (if any)
    $conn->query("DELETE FROM question_list WHERE staff_id = $staff_id");

    // Decode questions
    $questions = json_decode($questions, true);

    // Insert new questions
    foreach ($questions as $question) {
        $stmt = $conn->prepare("INSERT INTO question_list (staff_id, question) VALUES (?, ?)");
        $stmt->bind_param("is", $staff_id, $question);
        $stmt->execute();
        $stmt->close();
    }

    echo 1; // Success
} else {
    echo 0; // Error
}
?>
