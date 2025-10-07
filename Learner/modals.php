<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../output.css">
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    <title>Document</title>
    <style>
        body{
            padding:0;  
        }
            .modal-backdrop {
            background-color: var(--color-popup-bg);
        }
.modal-content-frame {
    background-color: var(--color-card-bg);
    border-left: 6px solid var(--color-heading);
    border-bottom: 6px solid var(--color-heading);
    border-radius: 1rem;

    /* Strong 3D shadow depth */
    box-shadow:
        0 8px 0 rgba(0, 0, 0, 0.2),         /* bottom border depth */
        0 10px 20px rgba(0, 0, 0, 0.25),    /* outer shadow */
        inset 0 1px 3px rgba(255, 255, 255, 0.3); /* subtle inner highlight */

    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

/* Optional hover effect for extra 3D pop */
.modal-content-frame:hover {
    transform: translateY(-3px);
    box-shadow:
        0 12px 0 rgba(0, 0, 0, 0.25),
        0 16px 30px rgba(0, 0, 0, 0.3),
        inset 0 1px 4px rgba(255, 255, 255, 0.3);
}

    </style>
</head>
<body>

<div class="fixed inset-0 flex items-center justify-center z-50 gap-3 modal-backdrop">
    <div class="modal-content-frame text-center p-8 rounded-xl w-full max-w-md space-y-6 border-[var(--color-heading)] border-2">
        <h3 class="text-3xl font-extrabold" style="color: var(--color-heading-secondary);"><i class="fas fa-medal text-3xl drop-shadow text-amber-400 mr-2"></i>Mastery Achieved!</h3>
        
       <p class="text-lg font-mono font-semibold leading-relaxed border p-2 rounded-lg border-[var(--color-icon)] border-l-4" style="color: var(--color-text);">
    Congratulations, brave learner! You’ve conquered every challenge and achieved a flawless 
    <span class="font-bold text-green-600">100% Mastery</span>. 
    Your dedication and skill have unlocked a new adventure — the journey continues!  
    </p>


        <div class="flex justify-center pt-4">
            <button class="font-bold py-3 px-6 rounded-lg transition-transform nav-btn" 
                style="background-color: var(--color-heading); color: white; box-shadow: 0 4px 0 var(--color-text-on-section);">
                 <i class="fas fa-arrow-right font-bold text-xl mr-2"></i>Continue
            </button>
        </div>
    </div>

    <div class="modal-content-frame text-center p-8 rounded-xl w-full max-w-md space-y-6 border-2 border-[var(--color-heading)]">
        
        
        <h3 class="text-3xl font-extrabold" style="color: var(--color-heading-secondary);"><i class="fas fa-hourglass-end text-3xl mr-2 drop-shadow text-red-500"></i>Quest Timer Depleted!
</h3>
        
       <p class="text-lg font-semibold font-mono leading-relaxed border-l-4 border-[var(--color-icon)] p-2 rounded-lg border" style="color: var(--color-text);">
    You’ve battled bravely against time itself, but the final moment has arrived.
    The system will now record your progress — your fate is sealed for this quest! 
    </p>


        <div class="flex justify-center pt-4">
            <button class="font-bold py-3 px-6 rounded-lg transition-transform nav-btn" 
                style="background-color: var(--color-heading); color: white; box-shadow: 0 4px 0 var(--color-text-on-section);">
                 <i class="fas fa-check-circle font-bold text-xl mr-2"></i>Submit
            </button>
        </div>
    </div>
    <div class="modal-content-frame text-center p-8 rounded-xl w-full max-w-md space-y-6 border-2 border-[var(--color-heading)]">
        
        
        <h3 class="text-3xl font-extrabold" style="color: var(--color-heading-secondary);"><i class="fas fa-heart-broken text-3xl mr-2 drop-shadow text-red-500"></i>Quest Failed.</h3>
        
       <p class="text-lg font-semibold font-mono leading-relaxed border border-l-4 p-2 rounded-lg border-[var(--color-icon)]" style="color: var(--color-text);">
All questions have been answered, but you haven’t passed this stage yet.
Sharpen your skills and try again later — the path to mastery awaits your return!
    </p>


        <div class="flex justify-center pt-4">
            <button class="font-bold py-3 px-6 rounded-lg transition-transform nav-btn" 
                style="background-color: var(--color-heading); color: white; box-shadow: 0 4px 0 var(--color-text-on-section);">
                <i class="fas fa-compass font-bold text-xl mr-2"></i> Return to Module
            </button>
        </div>
    </div>
</div>

</body>
</html>