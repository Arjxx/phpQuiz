<?php
include "config.php";
session_start();

// Ensure only the admin can access this page
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.php");
    exit;
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $quizName = $_POST['quiz_name'];
    
    $stmt = $conn->prepare("INSERT INTO quizzes (quiz_name) VALUES (?)");
    $stmt->execute([$quizName]);

    $message = "Quiz added successfully!";
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin - Add Quiz</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>

<div class="container mt-5">
    <h2>Admin Dashboard</h2>

    <div class="mt-4">
        <h4>Add a Quiz</h4>
        
        <?php if($message): ?>
            <div class="alert alert-success">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form action="admin.php" method="post">
            <div class="form-group">
                <label for="quiz_name">Quiz Name:</label>
                <input type="text" class="form-control" name="quiz_name" required>
            </div>
            <button type="submit" class="btn btn-primary">Add Quiz</button>
        </form>
    </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>