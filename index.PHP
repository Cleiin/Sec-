<?php
// index.php
include 'read_questions.php';

$questions = getQuestions('Formatted_Security.txt');
$totalQuestions = count($questions);

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max(1, min($totalQuestions, $page));

session_start();
if (!isset($_SESSION['answers'])) {
    $_SESSION['answers'] = array_fill(0, $totalQuestions, []);
    $_SESSION['score'] = 0;
}

if (isset($_POST['reset'])) {
    $_SESSION['answers'] = array_fill(0, $totalQuestions, []);
    $_SESSION['score'] = 0;
    header('Location: index.php?page=1');
    exit;
}

$currentQuestion = $questions[$page - 1];
$selectedAnswers = isset($_POST['answer']) ? $_POST['answer'] : [];
$isSubmitted = isset($_POST['submit']);

function isCorrect($selectedAnswers, $correctAnswers) {
    if (empty($selectedAnswers)) return false;
    sort($selectedAnswers);
    sort($correctAnswers);
    return $selectedAnswers === $correctAnswers;
}

if ($isSubmitted && empty($_SESSION['answers'][$page - 1])) {
    $_SESSION['answers'][$page - 1] = $selectedAnswers;
    $correctAnswers = str_split($currentQuestion['correct']);
    if (isCorrect($selectedAnswers, $correctAnswers)) {
        $_SESSION['score']++;
    }
}

$correctAnswers = str_split($currentQuestion['correct']);

function extractQuestionNumber($questionText) {
    if (preg_match('/NEW QUESTION (\d+)/', $questionText, $matches)) {
        return $matches[1];
    }
    return null;
}

function displayImages($questionNumber) {
    $images = [];
    $i = 1;
    while (file_exists("images/{$questionNumber}-{$i}.jpg")) {
        $images[] = "images/{$questionNumber}-{$i}.jpg";
        $i++;
    }
    if (empty($images) && file_exists("images/{$questionNumber}.jpg")) {
        $images[] = "images/{$questionNumber}.jpg";
    }
    return $images;
}

$questionNumber = extractQuestionNumber($currentQuestion['question']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Exam Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            display: flex;
            font-family: 'Roboto', sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            color: #333;
        }
        .sidebar {
            width: 220px;
            background-color: #2c3e50;
            color: #ecf0f1;
            height: 100vh;
            overflow-y: auto;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
            padding: 20px;
            box-sizing: border-box;
        }
        .sidebar a {
            display: block;
            margin: 5px 0;
            padding: 10px;
            background-color: #34495e;
            color: #ecf0f1;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        .sidebar a:hover {
            background-color: #1abc9c;
        }
        .sidebar a.active {
            background-color: #f1c40f;
            color: #fff;
        }
        .sidebar a.answered {
            background-color: #27ae60;
            color: #fff;
        }
        .sidebar a.wrong {
            background-color: #e74c3c;
            color: #fff;
        }
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            padding: 20px;
            box-sizing: border-box;
        }
        .score-container {
            background-color: #007bff;
            color: #fff;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .score-container button {
            background-color: #e74c3c;
            color: #fff;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .score-container button:hover {
            background-color: #c0392b;
        }
        .content {
            flex: 1;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            padding: 40px;
            border-radius: 8px;
            position: relative;
        }
        .question {
            margin-bottom: 20px;
        }
        .answers label {
            display: block;
            margin-bottom: 10px;
            font-size: 16px;
            padding: 10px;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        .answers label:hover {
            background-color: #ecf0f1;
        }
        .answers input[type="checkbox"] {
            margin-right: 10px;
        }
        .navigation {
            position: absolute;
            top: 20px;
            right: 20px;
        }
        .navigation a, .navigation button {
            margin-left: 10px;
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
            border: none;
            cursor: pointer;
        }
        .navigation a:hover, .navigation button:hover {
            background-color: #0056b3;
        }
        .submit-button {
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
            border: none;
            cursor: pointer;
        }
        .submit-button:hover {
            background-color: #0056b3;
        }
        .result {
            margin-top: 20px;
            padding: 20px;
            background-color: #ecf0f1;
            border-radius: 4px;
        }
        .result p {
            margin: 0;
            font-size: 16px;
        }
        .question-images img {
            max-width: 100%;
            height: auto;
            margin-top: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="sidebar" id="question-numbers">
        <?php for ($i = 1; $i <= $totalQuestions; $i++): ?>
            <?php
            $class = '';
            if (!empty($_SESSION['answers'][$i - 1])) {
                $class = isCorrect($_SESSION['answers'][$i - 1], str_split($questions[$i - 1]['correct'])) ? 'answered' : 'wrong';
            }
            ?>
            <a href="?page=<?php echo $i; ?>" class="<?php echo $i === $page ? 'active' : ''; ?> <?php echo $class; ?>">
                Question <?php echo $i; ?>
            </a>
        <?php endfor; ?>
    </div>
    <div class="main-content">
        <div class="score-container">
            <div>Score: <?php echo $_SESSION['score']; ?> / <?php echo $totalQuestions; ?></div>
            <form method="post" style="margin: 0;">
                <button type="submit" name="reset">Reset</button>
            </form>
        </div>
        <div class="content">
            <div class="navigation">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>" class="nav-link">Previous</a>
                <?php endif; ?>
                <?php if ($page < $totalQuestions): ?>
                    <a href="?page=<?php echo $page + 1; ?>" class="nav-link">Next</a>
                <?php endif; ?>
            </div>
            <div class="question">
                <h2><?php echo nl2br(htmlspecialchars($currentQuestion['question'])); ?></h2>
                <div class="question-images">
                    <?php
                    $images = displayImages($questionNumber);
                    foreach ($images as $image) {
                        echo "<img src=\"$image\" alt=\"Question $questionNumber Image\">";
                    }
                    ?>
                </div>
            </div>
            <form method="post">
                <div class="answers">
                    <?php foreach ($currentQuestion['answers'] as $answer): ?>
                        <label>
                            <input type="checkbox" name="answer[]" value="<?php echo htmlspecialchars($answer[0]); ?>" <?php echo in_array($answer[0], $selectedAnswers) ? 'checked' : ''; ?> <?php echo !empty($_SESSION['answers'][$page - 1]) ? 'disabled' : ''; ?>>
                            <?php echo htmlspecialchars($answer); ?>
                        </label>
                    <?php endforeach; ?>
                </div>
                <button type="submit" name="submit" class="submit-button" <?php echo !empty($_SESSION['answers'][$page - 1]) ? 'disabled' : ''; ?>>Submit</button>
            </form>
            <?php if ($isSubmitted): ?>
                <div class="result">
                    <?php if (isCorrect($selectedAnswers, $correctAnswers)): ?>
                        <p style="color: green;">Correct!</p>
                    <?php else: ?>
                        <p style="color: red;">Wrong! The correct answer is: <?php echo htmlspecialchars($currentQuestion['correct']); ?></p>
                    <?php endif; ?>
                    <p>Explanation: <?php echo nl2br(htmlspecialchars($currentQuestion['explanation'])); ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const questionNumbers = document.getElementById('question-numbers');
            
            // Restore scroll position
            const savedScrollPosition = localStorage.getItem('scrollPosition');
            if (savedScrollPosition) {
                questionNumbers.scrollTop = savedScrollPosition;
            }

            // Save scroll position
            questionNumbers.addEventListener('scroll', function() {
                localStorage.setItem('scrollPosition', questionNumbers.scrollTop);
            });

            document.querySelectorAll('.sidebar a, .nav-link').forEach(link => {
                link.addEventListener('click', function(event) {
                    event.preventDefault();
                    document.querySelector('.main-content').scrollIntoView({ behavior: 'smooth' });
                    window.location.href = this.href;
                });
            });
        });
    </script>
</body>
</html>
