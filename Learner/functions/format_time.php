<?php
function formatTime($minutes)
{
    $hours = floor($minutes / 60);
    $mins = $minutes % 60;

    if ($hours > 0 && $mins > 0) {
        return "$hours hr $mins mins";
    } elseif ($hours > 0) {
        return "$hours hr";
    } else {
        return "$mins mins"; 
    }
}
?>
