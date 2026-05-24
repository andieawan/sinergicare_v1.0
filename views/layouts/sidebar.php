<?php
require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../core/functions.php';
?>
<div class="hidden md:flex md:flex-shrink-0">
    <div class="flex flex-col w-64 bg-slate-900 text-white border-r border-slate-800">
        
        <div class="flex items-center justify-center h-16 px-4 bg-slate-950 font-bold text-xl tracking-wider text-emerald-400 gap-2 select-none">
            <span>🛡️</span> SinergiCare
        </div>
        
        <div class="flex flex-col flex-1 overflow-y-auto px-3 py-5 space-y-1 no-scrollbar">
            <p class="px-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2">Menu Navigasi</p>
            
            <a href="/pages/dashboard.php" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg text-slate-300 hover:bg-slate-800 hover:text-white transition-colors duration-150 group">
                <span class="mr-3 text-base group-hover:scale-110 transition-transform duration-150">📊</span> Dashboard
            </a>

            <?php if (hasRole(['super_admin', 'admin', 'bk', 'guru', 'waka_kesiswaan', 'kepala_jurusan'])): ?>
            <a href="/pages/jurnal.php" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg text-slate-300 hover:bg-slate-800 hover:text-white transition-colors duration-150 group">
                <span class="mr-3 text-base group-hover:scale-110 transition-transform duration-150">📝</span> Jurnal Insiden
            </a>
            <?php endif; ?>

            <?php if (hasRole(['super_admin', 'admin', 'bk'])): ?>
            <a href="/pages/bk.php" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg text-slate-300 hover:bg-slate-800 hover:text-white transition-colors duration-150 group">
                <span class="mr-3 text-base group-hover:scale-110 transition-transform duration-150">🧠</span> Panel Bimbingan BK
            </a>
            <?php endif; ?>

            <?php if (hasRole(['super_admin', 'admin', 'waka_kesiswaan'])): ?>
            <a href="/pages/waka.php" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg text-slate-300 hover:bg-slate-800 hover:text-white transition-colors duration-150 group">
                <span class="mr-3 text-base group-hover:scale-110 transition-transform duration-150">🏛️</span> Panel Kesiswaan (Waka)
            </a>
            <?php endif; ?>

            <?php if (hasRole(['super_admin', 'admin', 'kepala_jurusan'])): ?>
            <a href="/pages/kajur.php" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg text-slate-300 hover:bg-slate-800 hover:text-white transition-colors duration-150 group">
                <span class="mr-3 text-base group-hover:scale-110 transition-transform duration-150">🎓</span> Panel Ketua Jurusan
            </a>
            <?php endif; ?>

            <!-- NEW: Menu Cetak Surat Cepat untuk Admin & BK -->
            <?php if (hasRole(['super_admin', 'admin', 'bk'])): ?>
            <div class="pt-3 border-t border-slate-700 mt-3">
                <p class="px-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2">Cetak Dokumen</p>
                <a href="/pages/cetak_surat.php" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg text-slate-300 hover:bg-slate-800 hover:text-white transition-colors duration-150 group">
                    <span class="mr-3 text-base group-hover:scale-110 transition-transform duration-150">🖨️</span> Cetak Surat
                </a>
            </div>
            <?php endif; ?>

            <?php if (hasRole(['super_admin', 'admin'])): ?>
            <p class="px-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest pt-5 mb-2">Pengaturan</p>
            <a href="/pages/admin.php" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg text-slate-300 hover:bg-slate-800 hover:text-white transition-colors duration-150 group">
                <span class="mr-3 text-base group-hover:scale-110 transition-transform duration-150">⚙️</span> Kontrol Administrasi
            </a>
            <?php endif; ?>
        </div>

        <div class="flex-shrink-0 flex bg-slate-950 p-4 border-t border-slate-800 items-center justify-between">
            <div class="flex items-center min-w-0">
                <div class="w-8 h-8 rounded-full bg-emerald-500 text-slate-950 flex items-center justify-center font-bold text-sm flex-shrink-0 select-none">
                    <?php echo strtoupper(substr(currentUserName() ?: 'U', 0, 1)); ?>
                </div>
                <div class="ml-3 min-w-0">
                    <p class="text-xs font-semibold text-white truncate"><?php echo htmlspecialchars(currentUserName(), ENT_QUOTES, 'UTF-8'); ?></p>
                    <p class="text-[10px] font-medium text-slate-400 truncate">
                        <?php 
                        $roles = currentUserRoles();
                        echo htmlspecialchars(getRoleLabel($roles[0] ?? 'guest'), ENT_QUOTES, 'UTF-8');
                        ?>
                    </p>
                </div>
            </div>
            <a href="/logout.php" class="text-slate-400 hover:text-rose-400 p-1.5 rounded-lg hover:bg-slate-900 transition-colors duration-150" title="Keluar Aplikasi">
                🚪
            </a>
        </div>
    </div>
</div>

<div class="flex flex-col flex-1 w-0 overflow-hidden">
