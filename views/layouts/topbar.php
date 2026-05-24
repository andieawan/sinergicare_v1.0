<?php
require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../core/flash.php';
?>
<header class="flex-shrink-0 bg-white border-b border-slate-200 h-16 flex items-center justify-between px-6 z-10 shadow-sm">
    
    <div class="flex items-center space-x-3">
        <button class="md:hidden text-slate-500 hover:text-slate-600 focus:outline-none" title="Buka Menu">
            ☰
        </button>
        <h1 class="text-base font-semibold text-slate-800 tracking-tight md:text-lg">
            <?php echo htmlspecialchars($pageTitle ?? 'Dashboard', ENT_QUOTES, 'UTF-8'); ?>
        </h1>
    </div>

    <div class="flex items-center space-x-4">
        <div class="hidden lg:block text-xs font-medium text-slate-500 bg-slate-50 border border-slate-200 px-3 py-1.5 rounded-full select-none">
            📅 <?php echo date('d M Y'); ?>
        </div>

        <div class="text-[11px] text-slate-500 font-medium hidden sm:block">
            Status: <span class="bg-emerald-50 text-emerald-700 font-bold px-2 py-0.5 rounded border border-emerald-200 select-none">Terautentikasi</span>
        </div>
    </div>
</header>

<main class="flex-1 relative overflow-y-auto focus:outline-none p-6 bg-slate-50/50">
    <div class="max-w-7xl mx-auto space-y-6">
        
        <?php displayFlash(); ?>