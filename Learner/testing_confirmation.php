<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ISUtoLearn Testing Platform</title>
    <link rel="stylesheet" href="../output.css">
    <link rel="icon" href="../images/isu-logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body {
            font-family: 'Inter', sans-serif;
            background-color: var(--color-main-bg);
            color: var(--color-text);
            line-height: 1.5;
            font-size: 16px;
            height: 100vh; 
            overflow: auto; 
        }
        body { display: flex; flex-direction: column; padding:0;}

        .header {
            background-color: var(--color-header-bg);
            border-bottom: 1px solid var(--color-card-border);
            flex-shrink: 0;
            backdrop-filter: blur(5px);
            padding: 1rem 2rem; 
            position: sticky;
            top: 0;
            z-index: 10;
        }
        
        .main-content {
            flex: 1;
            display: flex;
            align-items: flex-start; 
            justify-content: center;
            padding: 2rem 1.5rem;
            min-height: 100vh; 
        }

        .authorization-card {
            background-color: var(--color-card-bg);
            border: 1px solid var(--color-card-border);
            border-top: 5px solid var(--color-heading-secondary); 
            border-radius: 8px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05); 
            max-width: 900px;
            width: 100%;
            display: flex;
            flex-direction: column;
            overflow: hidden; 
        }

        .status-bar {
            background-color: var(--color-card-section-bg);
            color: var(--color-text-on-section);
            padding: 1rem 2rem;
            border-bottom: 1px solid var(--color-card-border);
        }
        .status-pill {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
        }

        .core-content {
            padding: 2rem;
            display: grid;
            gap: 2rem;
            grid-template-columns: 1fr;
        }
        @media (min-width: 768px) {
            .core-content {
                grid-template-columns: 2fr 1fr;
            }
        }
        
        .info-block {
            border: 1px solid var(--color-card-border);
            border-radius: 6px;
            padding: 1.5rem;
        }

        .checklist-item {
            display: flex; align-items: flex-start; gap: 0.75rem; padding: 0.75rem 0;
            border-bottom: 1px solid var(--color-card-border);
        }
        .checklist-item:last-child { border-bottom: none; }
        
        .btn-base {
            transition: all 0.2s ease; font-weight: 600; border: none; font-size: 1rem;
            padding: 0.75rem 2rem; border-radius: 6px; cursor: pointer; text-align: center;
            display: inline-flex; align-items: center; justify-content: center;
        }
        .btn-primary { 
            background-color: var(--color-button-primary); color: white; 
            box-shadow: 0 3px 10px rgba(249, 115, 22, 0.2);
        }
        .btn-primary:hover:not(:disabled) { 
            background-color: var(--color-button-primary-hover); 
            box-shadow: 0 5px 15px rgba(249, 115, 22, 0.3); 
            transform: translateY(-1px);
        }
        .btn-secondary {
            background-color: var(--color-card-bg); 
            color: var(--color-text-secondary);
            border: 1px solid var(--color-card-border);
        }
        .btn-secondary:hover:not(:disabled) { 
            background-color: var(--color-card-section-bg); 
            color: var(--color-text);
        }
        
        .card-footer {
            flex-shrink: 0; 
            padding: 1.5rem 2rem; 
            border-top: 1px solid var(--color-card-border);
        }
        .recommended-badge {
            background-color: var(--color-heading-secondary);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-weight: 600;
            font-size: 0.8rem;
            display: inline-flex;
            align-items: center;
            box-shadow: 0 2px 5px rgba(249, 115, 22, 0.3);
        }

        .modal-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background-color: rgba(0, 0, 0, 0.5); display: none; justify-content: center;
            align-items: center; z-index: 1000; opacity: 0; transition: opacity 0.3s ease;
        }
        .modal-overlay.active { display: flex; opacity: 1; }
        .modal-content {
            background-color: white; padding: 25px; border-radius: 8px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2); max-width: 450px; width: 90%;
            transform: scale(0.95); transition: transform 0.3s ease;
            border-top: 4px solid var(--color-heading-secondary); 
        }
        .modal-overlay.active .modal-content { transform: scale(1); }
        .modal-header { color: var(--color-text); display: flex; align-items: center; margin-bottom: 1rem; }
        .modal-body { color: var(--color-text-secondary); margin-bottom: 1.5rem; }
    </style>
</head>
<body>
    <header class="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <div class="w-8 h-8 rounded-full object-contain flex items-center justify-center" style="background-color: var(--color-heading);">
                    <img src="../images/isu-logo.png" alt="">
                </div>
                <div>
                    <h1 class="text-lg font-bold" style="color: var(--color-text);">ISUtoLearn</h1>
                </div>
            </div>
            
            <div class="text-sm font-medium" style="color: var(--color-text-secondary);">
                <i class="fas fa-user-circle mr-1" style="color: var(--color-icon);"></i> User: Ryuta
            </div>
        </div>
    </header>

    <div class="main-content">
        <div class="authorization-card">
            
            <div class="status-bar flex flex-col md:flex-row md:justify-between md:items-center">
                <div class="flex items-center">
                    <h2 class="text-2xl font-bold" style="color: var(--color-text);">
                        <i class="fas fa-clipboard-check mr-2" style="color: var(--color-heading-secondary);"></i> Performance Assessment Module
                    </h2>
                </div>
                <div class="mt-3 md:mt-0">
                    <span class="recommended-badge">
                        <i class="fas fa-bolt mr-2"></i> Performance Evaluation
                    </span>
                </div>
            </div>

            <div class="core-content">
                
                <div class="left-pane">
                    <h3 class="text-xl font-semibold mb-3" style="color: var(--color-heading);">Module Summary</h3>
                    <p class="text-sm mb-6" style="color: var(--color-text-secondary);">
                        Assess your knowledge under realistic conditions with timed assessments and performance-based scoring to identify areas of strength and weakness.
                    </p>

                    <div class="info-block space-y-4" style="background-color: var(--color-card-section-bg);">
                        <h4 class="font-bold mb-3" style="color: var(--color-text-on-section);">Key Module Features</h4>
                        
                        <div class="checklist-item">
                            <i class="fas fa-clock text-lg" style="color: var(--color-correct);"></i>
                            <div>
                                <h5 class="font-medium" style="color: var(--color-text);">Timed Assessment Conditions</h5>
                                <p class="text-xs" style="color: var(--color-text-secondary);">Simulates real-world testing pressure.</p>
                            </div>
                        </div>
                        <div class="checklist-item">
                            <i class="fas fa-exclamation-triangle text-lg" style="color: var(--color-correct);"></i>
                            <div>
                                <h5 class="font-medium" style="color: var(--color-text);">Limited Attempt Simulations</h5>
                                <p class="text-xs" style="color: var(--color-text-secondary);">Focuses on readiness and accuracy.</p>
                            </div>
                        </div>
                        <div class="checklist-item">
                            <i class="fas fa-chart-bar text-lg" style="color: var(--color-correct);"></i>
                            <div>
                                <h5 class="font-medium" style="color: var(--color-text);">Performance Analytics & Scoring</h5>
                                <p class="text-xs" style="color: var(--color-text-secondary);">Immediate feedback on results.</p>
                            </div>
                        </div>
                        <div class="checklist-item">
                            <i class="fas fa-trophy text-lg" style="color: var(--color-correct);"></i>
                            <div>
                                <h5 class="font-medium" style="color: var(--color-text);">Competency Benchmarking</h5>
                                <p class="text-xs" style="color: var(--color-text-secondary);">Measures skill against required standards.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="right-pane">
                    <h3 class="text-xl font-semibold mb-4" style="color: var(--color-heading);">Assessment Configuration</h3>

                    <div class="bg-gray-50 p-4 rounded-lg space-y-3 border border-gray-200">
                        <div class="flex justify-between text-sm">
                            <span style="color: var(--color-text-secondary);">Best For:</span>
                            <span class="font-bold" style="color: var(--color-heading-secondary);">Skill Assessment</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span style="color: var(--color-text-secondary);">Assessment Pace:</span>
                            <span class="font-bold" style="color: var(--color-heading-secondary);">Timed</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span style="color: var(--color-text-secondary);">Duration:</span>
                            <span class="font-medium" style="color: var(--color-text);">45 Minutes</span>
                        </div>
                    </div>
                    
                    <div class="mt-6 p-4 rounded-lg" style="background-color: var(--color-button-secondary); color: var(--color-button-secondary-text);">
                        <p class="text-sm font-semibold flex items-center">
                            <i class="fas fa-history mr-2"></i> Last Score: 85% (Attempt 1/3)
                        </p>
                        <p class="text-xs mt-1">
                            You have 2 remaining attempts. Be sure to review prior feedback.
                        </p>
                    </div>

                </div>
            </div>
            
            <div class="card-footer">
                <div class="flex justify-end gap-4">
                    <button id="back-btn" class="btn-base btn-secondary">
                        <i class="fas fa-door-open mr-2"></i> Return to Selection
                    </button>
                    <button id="start-training-btn" class="btn-base btn-primary">
                        <i class="fas fa-stopwatch mr-2"></i> START TIMED ASSESSMENT
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div id="confirmation-modal" class="modal-overlay" role="dialog" aria-modal="true" aria-labelledby="modal-title">
        <div class="modal-content">
            <div class="modal-header">
                <i class="fas fa-exclamation-triangle text-xl mr-3" style="color: var(--color-heading-secondary);"></i>
                <h3 id="modal-title" class="font-bold">Abort Assessment?</h3>
            </div>
            <div class="modal-body">
                <p id="modal-message">Exiting now will count as a failed attempt and use up one of your limited simulation tries. Are you sure you want to exit?</p>
            </div>
            <div class="modal-footer flex justify-end gap-3">
                <button id="modal-cancel-btn" class="btn-base btn-secondary text-sm py-2 px-5">
                    Cancel (Continue)
                </button>
                <a href="course_assessment_mode.php" class="btn-base text-sm py-2 px-5" style="background-color: var(--color-heading-secondary); color: white;">
                    Confirm Abort
                </a>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const backBtn = document.getElementById('back-btn');
            const startTrainingBtn = document.getElementById('start-training-btn');
            const modalOverlay = document.getElementById('confirmation-modal');
            const modalCancelBtn = document.getElementById('modal-cancel-btn');
            
            const showModal = (title, message) => {
                document.getElementById('modal-title').textContent = title;
                document.getElementById('modal-message').textContent = message;
                modalOverlay.classList.add('active');
            };

            const hideModal = () => {
                modalOverlay.classList.remove('active');
            };

            modalCancelBtn.addEventListener('click', hideModal);

            modalOverlay.addEventListener('click', (e) => {
                if (e.target === modalOverlay) {
                    hideModal();
                }
            });

            backBtn.addEventListener('click', function() {
                showModal(
                    'Abort Assessment?',
                    'Exiting now will count as a failed attempt and use up one of your limited simulation tries. Are you sure you want to exit?'
                );
            });

            startTrainingBtn.addEventListener('click', function() {
                window.location.href = "testing_assessment_mode.php";
            });
        });
    </script>
</body>
</html>