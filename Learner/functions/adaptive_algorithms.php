<?php
// adaptive_algorithms.php
function check_student_mastery($topic_id, $choice_id) {
    global $pdo;

    if (!isset($_SESSION['answer_result_tracker'])) {
        $_SESSION['answer_result_tracker'] = [];
    }

    if (!isset($_SESSION['mastery_each_topic'][$topic_id])) {
        $_SESSION['mastery_each_topic'][$topic_id] = 0.3; // Start with 30% mastery
    }

    $stmt = $pdo->prepare("SELECT c.*, q.difficulty, q.id as question_id FROM choices c 
                          JOIN questions q ON c.question_id = q.id 
                          WHERE c.id = :choice_id AND q.topic_id = :topic_id");
    $stmt->execute([":topic_id" => $topic_id, ":choice_id" => $choice_id]);
    $choice = $stmt->fetch();

    if (!$choice) {
        return [
            'is_correct' => false,
            'streak' => 0,
            'topic_changed' => false,
            'new_mastery' => $_SESSION['mastery_each_topic'][$topic_id] ?? 0.3
        ];
    }

    // Track used questions
    if (!isset($_SESSION['used_id'][$topic_id])) {
        $_SESSION['used_id'][$topic_id] = [];
    }

    if (!in_array($choice['question_id'], $_SESSION['used_id'][$topic_id])) {
        $_SESSION['used_id'][$topic_id][] = $choice['question_id'];
    }

    // Calculate correct streak BEFORE adding current result
    $correct_streak = 0;
    for ($i = count($_SESSION['answer_result_tracker']) - 1; $i >= 0; $i--) {
        if ($_SESSION['answer_result_tracker'][$i]) {
            $correct_streak++;
        } else {
            break;
        }
    }

    // Add current result to tracker
    $_SESSION['answer_result_tracker'][] = $choice['is_correct'];

    // Get difficulty-based modification (INCREASED VALUES)
    $initial_mastery_mod = get_initial_mastery_modification($choice['difficulty']);

    if ($choice['is_correct']) {
        // Increase mastery with streak bonus (INCREASED BONUSES)
        // Note: Use the streak BEFORE current answer for calculation
        $mastery_increase = increase_mastery_by_correct_streak($initial_mastery_mod, $correct_streak);
        $_SESSION['mastery_each_topic'][$topic_id] += $mastery_increase;
    } else {
        // Decrease mastery (but less penalty)
        $_SESSION['mastery_each_topic'][$topic_id] -= ($initial_mastery_mod * 0.6); // 60% of increase as penalty
        
        // Reset streak when wrong answer
        // No need to reset $_SESSION['answer_result_tracker'] as it already contains the wrong answer
    }

    // Ensure mastery stays between 0 and 1
    $_SESSION['mastery_each_topic'][$topic_id] = max(0, min(1, $_SESSION['mastery_each_topic'][$topic_id]));

    // Check if we should move to next topic
    $topic_changed = check_mastery_progress($_SESSION['mastery_each_topic'][$topic_id]);

    return [
        'is_correct' => $choice['is_correct'],
        'streak' => $correct_streak + ($choice['is_correct'] ? 1 : 0),
        'topic_changed' => $topic_changed,
        'new_mastery' => $_SESSION['mastery_each_topic'][$topic_id],
        'mastery_change' => $choice['is_correct'] ? 
            increase_mastery_by_correct_streak($initial_mastery_mod, $correct_streak) : 
            -($initial_mastery_mod * 0.6)
    ];
}

function get_initial_mastery_modification($difficulty) {
    switch ($difficulty) {
        case 'easy':
            return 0.15;   // 15% for easy (increased)
        case 'medium':
            return 0.25;   // 25% for medium (increased)
        case 'hard':
            return 0.35;   // 35% for hard (increased)
        default:
            return 0.25;   // Default 25%
    }
}

function increase_mastery_by_correct_streak($initial_mastery, $streak) {
    // INCREASED STREAK BONUSES - 3-4 streaks should pass mastery
    $multipliers = [
        5 => 3.0,   // 5+ streak: 3x bonus
        4 => 2.5,   // 4 streak: 2.5x bonus
        3 => 2.0,   // 3 streak: 2x bonus (CAN PASS)
        2 => 1.5,   // 2 streak: 1.5x bonus
        1 => 1.0,   // 1 streak: normal
        0 => 1.0    // no streak: normal
    ];

    // Find the appropriate multiplier
    foreach ($multipliers as $min_streak => $multiplier) {
        if ($streak >= $min_streak) {
            return $initial_mastery * $multiplier;
        }
    }

    return $initial_mastery;
}

function check_mastery_progress($mastery) {
    // Move to next topic if mastery reaches 90%
    if ($mastery >= 0.9) {
        if (isset($_SESSION['topic_index']) && isset($_SESSION['topics_id'])) {
            if ($_SESSION['topic_index'] < (count($_SESSION['topics_id']) - 1)) {
                $_SESSION['topic_index'] += 1;
                
                // Update adaptive current topic index as well
                if (isset($_SESSION['adaptive_current_topic_index'])) {
                    $_SESSION['adaptive_current_topic_index'] = $_SESSION['topic_index'];
                }
                
                // Reset answer tracker for new topic
                $_SESSION['answer_result_tracker'] = [];
                
                return true; // Topic changed
            }
        }
    }
    return false; // Topic not changed
}