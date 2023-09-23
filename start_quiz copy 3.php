<?php
include "config.php";
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET["quiz_name"])) {
    header("Location: attend_quiz.php");
    exit;
}

// $subjectId = $_GET["subject_id"];
$quizname = $_GET["quiz_name"];

// Fetch questions and options based on subject
$stmt = $conn->prepare("
    SELECT q.question_id, q.question_text, o.option_id, o.option_text 
    FROM questions q 
    JOIN quizzes z ON q.quiz_id = z.quiz_id
    JOIN options o ON q.question_id = o.question_id
    WHERE z.subject_id = ?
");
$stmt->execute([$quizname]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$questions = [];
foreach ($rows as $row) {
    if (!isset($questions[$row['question_id']])) {
        $questions[$row['question_id']] = [
            'question_text' => $row['question_text'],
            'options' => []
        ];
    }
    $questions[$row['question_id']]['options'][$row['option_id']] = $row['option_text'];
}

$score = 0;
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    foreach ($questions as $questionId => $data) {
        $chosenOption = $_POST['question_' . $questionId];
        $stmt = $conn->prepare("SELECT is_correct FROM options WHERE option_id = ?");
        $stmt->execute([$chosenOption]);
        $result = $stmt->fetch();
        if ($result && $result['is_correct']) {
            $score++;
        }
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Start Quiz by Subject</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>

<div class="container mt-5">
    <h2>Start Quiz by Subject</h2>

    <?php if ($_SERVER["REQUEST_METHOD"] == "POST"): ?>
        <div class="alert alert-success">
            Your score is: <?php echo $score; ?> out of <?php echo count($questions); ?>
        </div>
    <?php else: ?>
        <form action="start_quiz.php?subject_id=<?php echo $subjectId; ?>" method="post">
            <?php foreach ($questions as $questionId => $data): ?>
                <div class="form-group">
                    <label><?php echo $data['question_text']; ?></label>
                    <?php foreach ($data['options'] as $optionId => $optionText): ?>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="question_<?php echo $questionId; ?>" value="<?php echo $optionId; ?>" required>
                            <label class="form-check-label"><?php echo $optionText; ?></label>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
            <button type="submit" class="btn btn-primary">Submit</button>
        </form>
    <?php endif; ?>

</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
