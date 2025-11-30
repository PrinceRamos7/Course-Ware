<?php
// Add these checks to prevent redeclaration errors
if (!function_exists('getExpTable')) {
    function getExpTable($maxExp, $maxLevel) {
        $levels = [];
        for ($n = 1; $n <= $maxLevel; $n++) {
            $exp = $maxExp * pow($n / $maxLevel, 2);
            $levels[$n] = round($exp);
        }
        return $levels;
    }
}

if (!function_exists('getUserLevel')) {
    function getUserLevel($userExp, $maxExp, $maxLevel) {
        $expTable = getExpTable($maxExp, $maxLevel);

        $level = 1;
        foreach ($expTable as $lvl => $expRequired) {
            if ($userExp >= $expRequired) {
                $level = $lvl;
            } else {
                break;
            }
        }

        if ($level >= $maxLevel) {
            return [
                $maxLevel,
                100,
                $userExp,
                null
            ];
        }

        $currentExpStart = $level === 1 ? 0 : $expTable[$level];
        $nextExp = $expTable[$level + 1];
        $progress = $nextExp - $currentExpStart > 0 ? ($userExp / $nextExp) * 100 : 0;
        $progress = max(0, min(100, $progress));

        return [
            $level,
            $progress,
            $userExp,
            $nextExp
        ];
    }
}
?>