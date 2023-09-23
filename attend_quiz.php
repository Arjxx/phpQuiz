<?php
include "config.php";
session_start();

// Ensure the user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$message = "";

// Fetch all subjects
$stmt = $conn->prepare("SELECT * FROM subjects");
$stmt->execute();
$subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);

$quizzes = [];
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['subject_id'])) {
    $subjectId = $_POST['subject_id'];
    $stmt = $conn->prepare("SELECT DISTINCT quiz_name FROM quizzes WHERE subject_id = ?");
    $stmt->execute([$subjectId]);
    $quizzes = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Attend Quiz</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>

<div class="container mt-5">
    <h2>Attend a Quiz</h2>

    <div class="mt-4">
        <form action="attend_quiz.php" method="post">
            <div class="form-group">
                <label for="subject_id">Select Subject:</label>
                <select class="form-control" name="subject_id" onchange="this.form.submit()">
                    <option value="">-- Select a Subject --</option>
                    <?php foreach ($subjects as $subject): ?>
                        <option value="<?php echo $subject['subject_id']; ?>"><?php echo $subject['subject_name']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>

        <?php if (!empty($quizzes)): ?>
            <h4>Available Quizzes:</h4>
            <ul>
                <?php foreach ($quizzes as $quiz): ?>
                    <li>
                        <a href="start_quiz.php?quiz_name=<?php echo $quiz['quiz_name']; ?>">
                            <?php echo $quiz['quiz_name']; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
