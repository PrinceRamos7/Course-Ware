<?php
function renderHeader($title) {
    $html = <<<HTML
<header class="header-bg shadow-lg p-4 flex justify-between items-center sticky top-0 z-10">
    <div class="fade-in">
        <h1 class="text-xl font-bold text-[var(--color-heading)]">$title</h1>
        <p class="text-sm text-[var(--color-text-secondary)]">Centralized system overview and management tools.</p>
    </div>
    <button class="flex items-center space-x-2 px-3 py-2 text-sm rounded-md shadow bg-gray-100 hover:bg-gray-200 transition hover-scale text-[var(--color-text)]">
        <i class="fas fa-user-circle text-xl text-[var(--color-heading)]"></i>
        <span class="font-semibold">Administrator</span>
    </button>
</header>
HTML;

    echo $html;
}
?>
