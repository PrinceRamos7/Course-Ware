<?php
include '../pdoconfig.php';

function get_completed_info($module_id, $topic_id = null) {
    global $pdo;

    if ($topic_id === null) {
        $stmt = $pdo->prepare('SELECT id FROM topics WHERE module_id = :module_id');
        $stmt->execute([":module_id" => $module_id]);
        $topics = $stmt->fetchAll();

        $total_exp = 0;
        $total_iexp = 0;
        $score_avg = 0;
        $total_topics = count($topics);
        $completed_topics = 0;
        foreach ($topics as $topic) {
            $stmt = $pdo->prepare(
            "SELECT tc.id, 
                        ss.last_score AS score,  
                        ss.exp_gained AS exp, 
                        ss.intelligent_exp_gained AS iexp,
                        a.total_items
                    FROM topics_completed tc
                    JOIN assessments a ON tc.topic_id = a.topic_id
                    JOIN student_score ss ON a.id = ss.assessment_id
                WHERE tc.topic_id = :topic_id AND tc.student_id = :student_id");
                $stmt->execute([":topic_id" => $topic['id'], ":student_id" => $_SESSION['student_id']]);
                $topic_info = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($topic_info) {
                $total_exp = $total_exp + $topic_info['exp'];
                $total_iexp = $total_iexp + $topic_info['iexp'];
                $score_avg = $score_avg + (($topic_info['score'] / $topic_info['total_items']) * 100);
                $completed_topics++;
            }
        }
        if ($total_topics > 0 && $completed_topics === $total_topics ) {
            return [
                'score' => number_format(($score_avg / $total_topics), 2),
                'exp' => $total_exp,
                'iexp' => $total_iexp
            ];
        } else {
            return null;
        }
        
    } else {
    $stmt = $pdo->prepare(
        "SELECT tc.id, 
                ss.last_score AS score,  
                ss.exp_gained AS exp, 
                ss.intelligent_exp_gained AS iexp,
                a.total_items
            FROM topics_completed tc
            JOIN assessments a ON tc.topic_id = a.topic_id
            JOIN student_score ss ON a.id = ss.assessment_id
        WHERE tc.topic_id = :topic_id AND tc.student_id = :student_id");
        $stmt->execute([":topic_id" => $topic_id, ":student_id" => $_SESSION['student_id']]);
        $topic_info = $stmt->fetch();

        if ($topic_info) {
            return [
                'score' => $topic_info['score'],
                'total_items' => $topic_info['total_items'],
                'exp' => $topic_info['exp'],
                'iexp' => $topic_info['iexp']
            ];
        } else {
            return null;
        }
    }
}

function get_completed_courses($student_id) {
    global $pdo;
    
    // Get all enrolled courses
    $stmt = $pdo->prepare(
        "SELECT c.id, c.title, c.description 
         FROM registration_code_uses rcu
         JOIN registration_codes rc ON rcu.registration_code_id = rc.id
         JOIN courses c ON rc.course_id = c.id
         WHERE rcu.student_id = :student_id"
    );
    $stmt->execute([":student_id" => $student_id]);
    $enrolled_courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $completed_courses = [];
    
    foreach ($enrolled_courses as $course) {
        $course_id = $course['id'];
        
        // Get all modules for this course
        $stmt = $pdo->prepare("SELECT id FROM modules WHERE course_id = :course_id");
        $stmt->execute([":course_id" => $course_id]);
        $modules = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $total_score = 0;
        $completed_modules_count = 0;
        $all_modules_completed = true;
        $latest_completion_date = null;
        
        foreach ($modules as $module) {
            $completion_info = get_completed_info($module['id']);
            
            if ($completion_info !== null) {
                // Module is completed
                $total_score += $completion_info['score'];
                $completed_modules_count++;
                
                // You might want to track completion dates in your topics_completed table
                // For now, we'll use current date or you can modify your completed_info function
            } else {
                // Module not completed
                $all_modules_completed = false;
            }
        }
        
        // If all modules are completed, add to completed courses
        if ($all_modules_completed && count($modules) > 0) {
            $average_score = $total_score / count($modules);
            $completed_courses[] = [
                'id' => $course_id,
                'name' => $course['name'],
                'description' => $course['description'],
                'final_score' => round($average_score, 1),
                'completion_date' => date('Y-m-d'), // You should track this in your database
                'modules_completed' => $completed_modules_count,
                'total_modules' => count($modules)
            ];
        }
    }
    
    return $completed_courses;
}

// Get completed courses for current student
$completed_courses = get_completed_courses($_SESSION['student_id']);
?>