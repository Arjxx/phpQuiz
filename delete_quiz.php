<?php
include "config.php";
session_start();

// Let's assume there's a session variable indicating if someone is an admin.
// Redirect non-admin users to a login or another appropriate page.
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.php");
    exit;
}


$message = "";

// Fetch distinct quiz names
$stmt = $conn->prepare("SELECT DISTINCT quiz_name FROM quizzes");
$stmt->execute();
$quizzes = $stmt->fetchAll(PDO::FETCH_ASSOC);


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['quiz_name'])) {
    $quizName = $_POST['quiz_name'];

    try {
        // Begin a transaction
        $conn->beginTransaction();

        // 1. Delete the options related to the questions of the quiz.
        $stmt = $conn->prepare("
            DELETE o 
            FROM options o 
            JOIN questions q ON o.question_id = q.question_id 
            JOIN quizzes z ON q.quiz_id = z.quiz_id
            WHERE z.quiz_name = ?
        ");
        $stmt->execute([$quizName]);

        // 2. Delete the questions of the quiz.
        $stmt = $conn->prepare("
            DELETE FROM questions 
            WHERE quiz_id IN (SELECT quiz_id FROM quizzes WHERE quiz_name = ?)
        ");
        $stmt->execute([$quizName]);

        // 3. Delete the quiz.
        $stmt = $conn->prepare("DELETE FROM quizzes WHERE quiz_name = ?");
        $stmt->execute([$quizName]);

        // Commit the transaction
        $conn->commit();

        $message = "Quizzes with the name '$quizName' deleted successfully!";

        // Refresh the quiz list
        $stmt = $conn->prepare("SELECT DISTINCT quiz_name FROM quizzes");
        $stmt->execute();
        $quizzes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Rollback the transaction in case of any errors
        $conn->rollback();
        $message = "Error deleting the quizzes: " . $e->getMessage();
    }
}



?>

<!DOCTYPE html>
<html>
<head>
    <title>Delete Quiz</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>

<div class="container mt-5">
    <h2>Delete a Quiz</h2>
    
    <?php if ($message): ?>
        <div class="alert alert-info">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <form action="delete_quiz.php" method="post">
    <div class="form-group">
        <label for="quiz_name">Select Quiz Name:</label>
        <select class="form-control" name="quiz_name">
            <?php foreach ($quizzes as $quiz): ?>
                <option value="<?php echo $quiz['quiz_name']; ?>"><?php echo $quiz['quiz_name']; ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <button type="submit" class="btn btn-danger">Delete Quizzes</button>
</form>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
