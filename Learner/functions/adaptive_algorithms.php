<?php
include '../pdoconfig.php';

$stmt = $pdo->prepare("SELECT topic_id FROM student_performance WHERE user_id = :student_id GROUP BY topic_id ORDER BY AVG(result) ASC");
$stmt->execute([":student_id" => $_SESSION['student_id']]);
$topic_ids = $stmt->fetchAll();

$_SESSION['topics_id'] = array_column($topic_ids, 'topic_id');

if (!isset($_SESSION['topic_index'])) {
    $_SESSION['topic_index'] = 0;
}

if (!isset($_SESSION['used_id'])) {
    $_SESSION['used_id'] = [];
}
if (!isset($_SESSION['mastery_each_topic'])) {
    $_SESSION['mastery_each_topic'] = [];
}

//return topic id to use to get the question
function check_student_mastery($topic_id, $choice_id) {
    global $pdo;

    if (!isset($_SESSION['answer_result_tracker'])) {
        $_SESSION['answer_result_tracker'] = [];
    }

    if (empty($_SESSION['mastery_each_topic'][$topic_id])) {
        $_SESSION['mastery_each_topic'][$topic_id] = 0.3;
    }

    $stmt = $pdo->prepare("SELECT  * FROM choices WHERE topic_id = :topic_id AND id = :choice_id");
    $stmt->execute([":topic_id" => $topic_id, ":choice_id" => $choice_id]);
    $choice = $stmt->fetch();

    if (!isset($_SESSION['used_id'][$topic_id])) {
        $_SESSION['used_id'][$topic_id] = [];
    }

    if (!in_array($choice_id, $_SESSION['used_id'][$topic_id])) {
        $_SESSION['used_id'][$topic_id][] = $choice['question_id'];
    } 

    $correct_streak = 0;
    for ($i = count($_SESSION['answer_result_tracker']) - 1; $i >= 0; $i--) {
        if  ($_SESSION['answer_result_tracker'][$i]) {
            $correct_streak++;
        } else {
            break;
        }
    }
    $_SESSION['answer_result_tracker'][] = $choice['is_correct'];

    $stmt = $pdo->prepare("SELECT difficulty FROM questions WHERE id = :question_id");
    $stmt->execute([":question_id" => $choice['question_id']]);
    $question = $stmt->fetch();

    if ($choice['is_correct']) {
        $initial_mastery_increase = get_initial_mastery_modification($question['difficulty']);
        $_SESSION['mastery_each_topic'][$topic_id] += increase_mastery_by_correct_streak($initial_mastery_increase, $correct_streak);
    } else {
        $_SESSION['mastery_each_topic'][$topic_id] -= get_initial_mastery_modification($question['difficulty']);
    }

    $_SESSION['mastery_each_topic'][$topic_id] = max(0, min(1, $_SESSION['mastery_each_topic'][$topic_id]));
    
    check_mastery_progress($_SESSION['mastery_each_topic'][$topic_id]);
}

function get_initial_mastery_modification($difficulty) {
    if ($difficulty == 'easy') {
        return 0.50;
    } elseif ($difficulty == 'medium') {
        return 0.10;
    } elseif ($difficulty == 'hard') {
        return 0.15;
    } else {
        return 0.10;
    }
}

function increase_mastery_by_correct_streak($initial_mastery, $streak) {
    if ($streak >= 5) {
        return $initial_mastery * 2;
    } elseif ($streak >= 4) {
        return $initial_mastery * 1.75;
    } elseif ($streak >= 3) {
        return $initial_mastery * 1.5;
    } elseif ($streak >= 2) {
        return $initial_mastery * 1.25;
    } else {
        return $initial_mastery;
    }
}

function check_mastery_progress($mastery) {
    if ($mastery >= 0.9) {
        if ($_SESSION['topic_index'] < (count($_SESSION['topics_id'] ) - 1)) {
            $_SESSION['topic_index'] += 1;
        }
    }
}
?>