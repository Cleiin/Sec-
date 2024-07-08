<?php
// read_questions.php

function getQuestions($filePath) {
    $questions = [];
    $currentQuestion = null;
    $file = fopen($filePath, "r");

    if ($file) {
        while (($line = fgets($file)) !== false) {
            if (strpos($line, "NEW QUESTION") === 0) {
                if ($currentQuestion) {
                    $questions[] = $currentQuestion;
                }
                $currentQuestion = [
                    'question' => '',
                    'answers' => [],
                    'correct' => '',
                    'explanation' => ''
                ];
            }
            if ($currentQuestion !== null) {
                if (strpos($line, "- (Exam Topic") === 0) {
                    $currentQuestion['question'] .= $line;
                } elseif (preg_match('/^[A-Z]\./', $line)) {
                    $currentQuestion['answers'][] = $line;
                } elseif (strpos($line, "Answer:") === 0) {
                    $currentQuestion['correct'] = trim(str_replace("Answer:", "", $line));
                } elseif (strpos($line, "Explanation:") === 0) {
                    $currentQuestion['explanation'] = substr($line, 12); // Skip "Explanation: "
                } else {
                    if ($currentQuestion['correct']) {
                        $currentQuestion['explanation'] .= $line;
                    } else {
                        $currentQuestion['question'] .= $line;
                    }
                }
            }
        }
        if ($currentQuestion) {
            $questions[] = $currentQuestion;
        }
        fclose($file);
    }
    return $questions;
}
?>
