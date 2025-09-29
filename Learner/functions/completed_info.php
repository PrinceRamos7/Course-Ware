<?php
include '../pdoconfig.php';

function get_completed_info($module_id, $topic_id = null) {
    global $pdo;

    if ($topic_id === null) {
        $stmt = $pdo->prepare('SELECT id FROM topicS WHERE module_id = :module_id');
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
?>