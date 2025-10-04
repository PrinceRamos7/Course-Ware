<?php 
function getRankedAssessmentsQuestions($pdo, $user_id, $limit_per_assessment = 20){
    $stmt = $pdo->prepare("SELECT a.id AS assessment_id, a.name, a.type, a.topic_id, a.module_id,
            COALESCE(ROUND(SUM(sp.result)/COUNT(sp.id),2),0) AS accuracy
        FROM assessments a
        LEFT JOIN student_performance sp
            ON sp.assessment_id = a.id AND sp.user_id = :user_id
        GROUP BY a.id
        ORDER BY accuracy DESC, a.id ASC
    ");
    $stmt->execute([':user_id' => $user_id]);
    $assessments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $result = [];

    foreach($assessments as $assess){
        $stmt2 = $pdo->prepare("SELECT sp.topic_id,
                COALESCE(ROUND(SUM(sp.result)/COUNT(sp.id),2),0) AS topic_accuracy
            FROM questions q
            LEFT JOIN student_performance sp
                ON sp.user_id = :user_id AND sp.assessment_id = q.assessment_id
                AND sp.topic_id = q.topic_id
            WHERE q.assessment_id = :assessment_id
            GROUP BY q.topic_id
            ORDER BY topic_accuracy DESC
        ");
        $stmt2->execute([
            ':user_id' => $user_id,
            ':assessment_id' => $assess['assessment_id']
        ]);
        $topics = $stmt2->fetchAll(PDO::FETCH_ASSOC);

        $questions = [];
        if(count($topics) > 0){
            foreach($topics as $t){
                $stmt3 = $pdo->prepare("SELECT id, assessment_id, question, topic_id
                    FROM questions
                    WHERE assessment_id = :assessment_id
                    AND topic_id = :topic_id
                    ORDER BY RAND()
                    LIMIT 5
                ");
                $stmt3->execute([
                    ':assessment_id' => $assess['assessment_id'],
                    ':topic_id' => $t['topic_id']
                ]);
                $questions = array_merge($questions, $stmt3->fetchAll(PDO::FETCH_ASSOC));
            }
        } else {
            $stmt3 = $pdo->prepare("
                SELECT id, assessment_id, question, topic_id
                FROM questions
                WHERE assessment_id = :assessment_id
                ORDER BY RAND()
            ");
            $stmt3->execute([':assessment_id' => $assess['assessment_id']]);
            $questions = $stmt3->fetchAll(PDO::FETCH_ASSOC);
        }

        $result[] = [
            'assessment' => $assess,
            'questions' => array_slice($questions, 0, $limit_per_assessment)
        ];
    }

    return $result;
}
?>