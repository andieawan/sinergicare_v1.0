</div> </main> <footer class="flex-shrink-0 bg-white border-t border-slate-200 h-10 flex items-center justify-between px-6 text-[11px] text-slate-400 font-medium select-none z-10">
        <div>
            &copy; <?php echo date('Y'); ?> <span class="text-slate-600 font-semibold">SinergiCare</span>. Hak Cipta Dilindungi.
        </div>
        <div class="hidden sm:block">
            Sistem Manajemen Kedisiplinan Siswa v3.0
        </div>
    </footer>

    </div> </div> 
    
<script>
    // Logika Navigasi Responsif Menu Mobile (Sidebar Toggle)
    document.addEventListener('DOMContentLoaded', () => {
        const btnToggle = document.querySelector('button[title="Buka Menu"]');
        const sidebar = document.getElementById('sidebar');
        
        if (btnToggle && sidebar) {
            btnToggle.addEventListener('click', (e) => {
                e.stopPropagation();
                // Mendukung pendekatan layout off-canvas slide ataupun display state hidden
                sidebar.classList.toggle('hidden');
                sidebar.classList.toggle('-translate-x-full');
            });
            
            // Opsional: Menutup sidebar secara otomatis jika pengguna mengklik area luar menu (mobile overlay)
            document.addEventListener('click', (e) => {
                if (!sidebar.contains(e.target) && !btnToggle.contains(e.target) && !sidebar.classList.contains('-translate-x-full')) {
                    sidebar.classList.add('-translate-x-full');
                    sidebar.classList.add('hidden');
                }
            });
        }
    });
</script>
</body>
</html>