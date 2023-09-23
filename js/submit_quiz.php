<?php
require 'config.php';  // Assuming you've a separate file for database connection

$data = json_decode(file_get_contents("php://input"));

$score = $data->score;
$answers = $data->answers;
$userID = $_SESSION['userID'];  // Assuming you're using sessions to keep track of logged-in user

try {
    // Store the score in a 'results' table
    $stmt = $conn->prepare("INSERT INTO results (user_id, score) VALUES (?, ?)");
    $stmt->execute([$userID, $score]);

    // If you want to store individual answers, you can iterate over $answers and insert them
    foreach ($answers as $questionID => $answer) {
        $stmt = $conn->prepare("INSERT INTO user_answers (user_id, question_id, answer) VALUES (?, ?, ?)");
        $stmt->execute([$userID, $questionID, $answer]);
    }

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
