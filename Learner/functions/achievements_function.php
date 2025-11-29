<?php
// functions/achievements_functions.php

function checkAndAwardAchievements($student_id, $pdo) {
    $achievements_query = "SELECT * FROM achievements WHERE id NOT IN (SELECT achievement_id FROM student_achievements WHERE student_id = ?)";
    $stmt = $pdo->prepare($achievements_query);
    $stmt->execute([$student_id]);
    $locked_achievements = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $newly_unlocked = [];
    
    foreach ($locked_achievements as $achievement) {
        if (checkAchievementRequirement($student_id, $achievement, $pdo)) {
            // Award the achievement
            $award_stmt = $pdo->prepare("INSERT INTO student_achievements (student_id, achievement_id) VALUES (?, ?)");
            $award_stmt->execute([$student_id, $achievement['id']]);
            
            // Update user XP and intelligence
            $update_stmt = $pdo->prepare("UPDATE users SET experience = experience + ?, intelligent_exp = intelligent_exp + ? WHERE id = ?");
            $update_stmt->execute([$achievement['xp_reward'], $achievement['intelligence_reward'], $student_id]);
            
            $newly_unlocked[] = $achievement;
        }
    }
    
    return $newly_unlocked;
}

function checkAchievementRequirement($student_id, $achievement, $pdo) {
    switch ($achievement['requirement_type']) {
        case 'course_completion':
            if ($achievement['requirement_value'] === '1') {
                // First course completion
                $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM student_courses WHERE student_id = ?");
                $stmt->execute([$student_id]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                return $result['count'] >= 1;
            }
            // Add more course-specific checks as needed
            break;
            
        case 'perfect_score':
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM student_score WHERE student_id = ? AND last_score = total_items");
            $stmt->execute([$student_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] > 0;
            
        case 'streak':
            $stmt = $pdo->prepare("SELECT current_streak FROM student_login_streak WHERE student_id = ? ORDER BY updated_at DESC LIMIT 1");
            $stmt->execute([$student_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result && $result['current_streak'] >= $achievement['requirement_value'];
            
        // Add more requirement types as needed
    }
    
    return false;
}
?>