<?php
include '../pdoconfig.php';

// Check if class already exists to prevent redeclaration
if (!class_exists('DailyGoalsSystem')) {

class DailyGoalsSystem {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    // Get today's goals with accurate tracking
    public function getTodaysGoals($student_id) {
        $today = date('Y-m-d');
        
        // Check if we have goals for today
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) as goal_count 
             FROM student_daily_goals 
             WHERE student_id = :student_id AND date = :today"
        );
        $stmt->execute([':student_id' => $student_id, ':today' => $today]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // If no goals for today, generate random ones
        if ($result['goal_count'] == 0) {
            $this->generateRandomDailyGoals($student_id);
        }
        
        // Get today's goals
        $stmt = $this->pdo->prepare(
            "SELECT dg.*, 
                    sdg.progress_current, 
                    sdg.is_completed,
                    sdg.claimed_reward,
                    CASE 
                        WHEN sdg.progress_current >= dg.target_value THEN 100
                        ELSE ROUND((sdg.progress_current / dg.target_value) * 100, 1)
                    END as progress_percentage
             FROM daily_goals dg
             JOIN student_daily_goals sdg ON dg.id = sdg.daily_goal_id 
             WHERE sdg.student_id = :student_id 
             AND sdg.date = :today
             ORDER BY sdg.is_completed ASC, dg.category ASC"
        );
        $stmt->execute([':student_id' => $student_id, ':today' => $today]);
        $goals = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $goals;
    }
    
    // Update goal progress with accurate completion tracking
    public function updateGoalProgress($student_id, $goal_type, $progress_amount = 1) {
        $today = date('Y-m-d');
        
        // First, get current progress for all goals of this type
        $stmt = $this->pdo->prepare(
            "SELECT sdg.id, sdg.progress_current, sdg.is_completed, dg.target_value
             FROM student_daily_goals sdg
             JOIN daily_goals dg ON sdg.daily_goal_id = dg.id
             WHERE sdg.student_id = :student_id 
             AND sdg.date = :today 
             AND dg.type = :goal_type
             AND sdg.is_completed = FALSE"  // Only update incomplete goals
        );
        $stmt->execute([
            ':student_id' => $student_id,
            ':today' => $today,
            ':goal_type' => $goal_type
        ]);
        $goals = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $claimed_rewards = [];
        
        foreach ($goals as $goal) {
            $new_progress = $goal['progress_current'] + $progress_amount;
            $is_completed = $new_progress >= $goal['target_value'];
            
            // Update progress
            $updateStmt = $this->pdo->prepare(
                "UPDATE student_daily_goals 
                 SET progress_current = :progress,
                     is_completed = :completed,
                     updated_at = NOW()
                 WHERE id = :id"
            );
            $updateStmt->execute([
                ':progress' => min($new_progress, $goal['target_value']), // Don't exceed target
                ':completed' => $is_completed ? 1 : 0,
                ':id' => $goal['id']
            ]);
            
            // If goal is newly completed, claim reward immediately
            if ($is_completed && !$goal['is_completed']) {
                $this->claimRewardForGoal($student_id, $goal['id']);
                $claimed_rewards[] = $goal['id'];
            }
        }
        
        return count($claimed_rewards); // Return number of newly claimed rewards
    }
    
    // Claim reward for a specific goal
    private function claimRewardForGoal($student_id, $goal_record_id) {
        $today = date('Y-m-d');
        
        $this->pdo->beginTransaction();
        
        try {
            // Get goal details and verify it's completed but not claimed
            $stmt = $this->pdo->prepare(
                "SELECT dg.reward_exp, dg.reward_intelligent_exp 
                 FROM student_daily_goals sdg
                 JOIN daily_goals dg ON sdg.daily_goal_id = dg.id
                 WHERE sdg.id = :id 
                 AND sdg.student_id = :student_id
                 AND sdg.date = :today
                 AND sdg.is_completed = TRUE 
                 AND sdg.claimed_reward = FALSE"
            );
            $stmt->execute([
                ':id' => $goal_record_id,
                ':student_id' => $student_id,
                ':today' => $today
            ]);
            $goal = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($goal) {
                // Add rewards
                $this->addStudentRewards($student_id, $goal['reward_exp'], $goal['reward_intelligent_exp']);
                
                // Mark as claimed
                $stmt = $this->pdo->prepare(
                    "UPDATE student_daily_goals 
                     SET claimed_reward = TRUE,
                         updated_at = NOW()
                     WHERE id = :id"
                );
                $stmt->execute([':id' => $goal_record_id]);
                
                $this->pdo->commit();
                
                // Log successful claim
                error_log("Auto-claimed reward for student $student_id, goal record $goal_record_id");
                
                return [
                    'success' => true,
                    'exp' => $goal['reward_exp'],
                    'intelligent_exp' => $goal['reward_intelligent_exp']
                ];
            }
            
            $this->pdo->rollBack();
            return ['success' => false, 'message' => 'Goal not found or already claimed'];
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Error claiming reward: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error claiming reward'];
        }
    }
    
    // Add rewards to student
    private function addStudentRewards($student_id, $exp, $intelligent_exp) {
        $stmt = $this->pdo->prepare(
            "UPDATE users 
             SET experience = COALESCE(experience, 0) + :exp,
                 intelligent_exp = COALESCE(intelligent_exp, 0) + :iexp
             WHERE id = :student_id"
        );
        $stmt->execute([
            ':exp' => $exp,
            ':iexp' => $intelligent_exp,
            ':student_id' => $student_id
        ]);
    }
    
    // Generate random daily goals
    private function generateRandomDailyGoals($student_id) {
        $today = date('Y-m-d');
        
        $stmt = $this->pdo->prepare(
            "SELECT id FROM daily_goals 
             WHERE is_active = TRUE 
             ORDER BY RAND() 
             LIMIT 3" 
        );
        $stmt->execute();
        $randomGoals = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $insertStmt = $this->pdo->prepare(
            "INSERT INTO student_daily_goals (student_id, daily_goal_id, date) 
             VALUES (:student_id, :goal_id, :today)"
        );
        
        foreach ($randomGoals as $goal) {
            $insertStmt->execute([
                ':student_id' => $student_id,
                ':goal_id' => $goal['id'],
                ':today' => $today
            ]);
        }
        
        return count($randomGoals);
    }
    
    // Get today's completion stats
    public function getTodaysCompletionStats($student_id) {
        $today = date('Y-m-d');
        
        $stmt = $this->pdo->prepare(
            "SELECT 
                COUNT(*) as total_goals,
                SUM(CASE WHEN sdg.is_completed = TRUE THEN 1 ELSE 0 END) as completed_goals,
                SUM(CASE WHEN sdg.claimed_reward = TRUE THEN 1 ELSE 0 END) as claimed_rewards
             FROM student_daily_goals sdg
             JOIN daily_goals dg ON sdg.daily_goal_id = dg.id
             WHERE sdg.student_id = :student_id AND sdg.date = :today"
        );
        $stmt->execute([':student_id' => $student_id, ':today' => $today]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Update login streak
    public function updateLoginStreak($student_id) {
        $today = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        
        $stmt = $this->pdo->prepare(
            "SELECT * FROM student_login_streak WHERE student_id = :student_id"
        );
        $stmt->execute([':student_id' => $student_id]);
        $streak = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$streak) {
            $stmt = $this->pdo->prepare(
                "INSERT INTO student_login_streak (student_id, last_login_date) VALUES (:student_id, :today)"
            );
            $stmt->execute([':student_id' => $student_id, ':today' => $today]);
        } else {
            if ($streak['last_login_date'] == $today) {
                return $streak['current_streak'];
            } elseif ($streak['last_login_date'] == $yesterday) {
                $new_streak = $streak['current_streak'] + 1;
                $longest_streak = max($new_streak, $streak['longest_streak']);
                
                $stmt = $this->pdo->prepare(
                    "UPDATE student_login_streak 
                     SET last_login_date = :today, 
                         current_streak = :current_streak,
                         longest_streak = :longest_streak
                     WHERE student_id = :student_id"
                );
                $stmt->execute([
                    ':today' => $today,
                    ':current_streak' => $new_streak,
                    ':longest_streak' => $longest_streak,
                    ':student_id' => $student_id
                ]);
                
                $this->updateGoalProgress($student_id, 'login_streak');
                return $new_streak;
            } else {
                $stmt = $this->pdo->prepare(
                    "UPDATE student_login_streak 
                     SET last_login_date = :today, 
                         current_streak = 1
                     WHERE student_id = :student_id"
                );
                $stmt->execute([':today' => $today, ':student_id' => $student_id]);
                return 1;
            }
        }
    }
}

} // End of class_exists check
?>