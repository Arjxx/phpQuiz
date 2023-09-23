<?php
include "config.php";
session_start();

// Ensure only the admin can access this page
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.php");
    exit;
}

$message = "";

// Fetch all subjects
$stmt = $conn->prepare("SELECT * FROM subjects");
$stmt->execute();
$subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $subjectId = $_POST['subject_id'];
    
    for ($i = 1; $i <= 2; $i++) {
        if (!empty($_POST['quiz_name_' . $i])) {
            $quizName = $_POST['quiz_name_' . $i];
            $stmt = $conn->prepare("INSERT INTO quizzes (subject_id, quiz_name) VALUES (?, ?)");
            $stmt->execute([$subjectId, $quizName]);
            
            $quizId = $conn->lastInsertId();
            
            $questionText = $_POST['question_text_' . $i];
            $stmt = $conn->prepare("INSERT INTO questions (quiz_id, question_text) VALUES (?, ?)");
            $stmt->execute([$quizId, $questionText]);
            
            $questionId = $conn->lastInsertId();

            for ($j = 1; $j <= 4; $j++) {
                $optionText = $_POST['option_' . $i . '_' . $j];
                $isCorrect = ($_POST['correct_answer_' . $i] == $j) ? 1 : 0;

                $stmt = $conn->prepare("INSERT INTO options (question_id, option_text, is_correct) VALUES (?, ?, ?)");
                $stmt->execute([$questionId, $optionText, $isCorrect]);
            }
        }
    }
    $message = "Quizzes added successfully!";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin - Add Quizzes</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>

<div class="container mt-5">
    <h2>Admin Dashboard</h2>

    <div class="mt-4">
        <h4>Add Quizzes</h4>
        
        <?php if ($message): ?>
            <div class="alert alert-success">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form action="admin.php" method="post">

        <label for="quiz_name ?>">Quiz Name:</label>
            <input type="text" class="form-control mb-2" name="quiz_name">
            <div class="form-group">
                <label for="subject_id">Select Subject:</label>
                <select class="form-control" name="subject_id" required>
                    <?php foreach ($subjects as $subject): ?>
                        <option value="<?php echo $subject['subject_id']; ?>"><?php echo $subject['subject_name']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <?php for ($i = 1; $i <= 2; $i++): ?>
                <div class="form-group">
                    
                    <label for="question_text_<?php echo $i; ?>">Question:</label>
                    <input type="text" class="form-control mb-2" name="question_text_<?php echo $i; ?>">
                    
                    <p>Options:</p>
                    <?php for ($j = 1; $j <= 4; $j++): ?>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="correct_answer_<?php echo $i; ?>" value="<?php echo $j; ?>" required>
                            <input type="text" class="form-control mb-2" name="option_<?php echo $i; ?>_<?php echo $j; ?>" placeholder="Option <?php echo $j; ?>">
                        </div>
                    <?php endfor; ?>
                </div>
            <?php endfor; ?>

            <button type="submit" class="btn btn-primary">Add Quizzes</button>
        </form>
    </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
