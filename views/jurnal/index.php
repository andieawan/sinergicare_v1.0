<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    
    <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm h-fit">
        <h3 class="text-sm font-bold text-slate-800 tracking-tight mb-1">📢 Laporkan Insiden Baru</h3>
        <p class="text-xs text-slate-400 mb-4">Pastikan data kronologi dan identifikasi siswa diisi secara valid.</p>
        
        <form action="/modules/jurnal/store.php" method="POST" class="space-y-4">
            <div>
                <label for="student_input" class="block text-xs font-semibold text-slate-600 mb-1">Cari Nama / NISN Siswa</label>
                <input list="student_list" id="student_input" name="student_info" autocomplete="off" required
                       placeholder="Ketik nama atau NISN..." 
                       class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-slate-800 focus:outline-none focus:border-indigo-500 transition-colors">
                <datalist id="student_list">
                    <?php foreach ($students as $stu): ?>
                        <option value="<?php echo htmlspecialchars($stu['nisn'] . ' - ' . $stu['nama'] . ' [' . ($stu['nama_kelas'] ?? 'Tanpa Kelas') . ']', ENT_QUOTES, 'UTF-8'); ?>">
                    <?php endforeach; ?>
                </datalist>
            </div>

            <div>
                <label for="category_id" class="block text-xs font-semibold text-slate-600 mb-1">Jenis Kejadian / Pelanggaran</label>
                <select id="category_id" name="category_id" required
                        class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-slate-800 focus:outline-none focus:border-indigo-500 transition-colors">
                    <option value="" disabled selected>-- Pilih Kategori Kasus --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>">
                            [<?php echo strtoupper($cat['bobot_risiko']); ?>] <?php echo htmlspecialchars($cat['nama_kejadian'], ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="tanggal_kejadian" class="block text-xs font-semibold text-slate-600 mb-1">Tanggal</label>
                    <!-- BUG FIX: date('Y-md') → date('Y-m-d') -->
                    <input type="date" id="tanggal_kejadian" name="tanggal_kejadian" required value="<?php echo date('Y-m-d'); ?>"
                           class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-slate-800 focus:outline-none focus:border-indigo-500 transition-colors">
                </div>
                <div>
                    <label for="lokasi_kejadian" class="block text-xs font-semibold text-slate-600 mb-1">Lokasi TKP</label>
                    <input type="text" id="lokasi_kejadian" name="lokasi_kejadian" required placeholder="Misal: Kelas X-1, Kantin"
                           class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-slate-800 focus:outline-none focus:border-indigo-500 transition-colors">
                </div>
            </div>

            <div>
                <label for="catatan" class="block text-xs font-semibold text-slate-600 mb-1">Catatan Ringkas / Kronologi Kasus</label>
                <textarea id="catatan" name="catatan" rows="3" required placeholder="Gambarkan deskripsi singkat situasi kejadian..."
                          class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-slate-800 focus:outline-none focus:border-indigo-500 transition-colors"></textarea>
            </div>

            <button type="submit" class="w-full bg-slate-900 text-white font-bold text-xs py-3 rounded-xl hover:bg-slate-800 transition-colors shadow-sm tracking-wide">
                🚀 Catat & Submit Jurnal
            </button>
        </form>
    </div>

    <div class="lg:col-span-2 bg-white border border-slate-200 rounded-2xl p-6 shadow-sm flex flex-col justify-between">
        <div>
            <h3 class="text-sm font-bold text-slate-800 tracking-tight">📜 Log Riwayat Kasus Terdaftar</h3>
            <p class="text-xs text-slate-400 mb-4">Daftar menyeluruh kasus perilaku ketertiban yang terekam di sistem.</p>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-slate-700 uppercase font-bold border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-3">Siswa & Kelas</th>
                            <th class="py-3 px-3">Detail Insiden</th>
                            <th class="py-3 px-3 text-center">Risiko</th>
                            <th class="py-3 px-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (empty($incidents)): ?>
                            <tr>
                                <td colspan="4" class="py-6 text-center text-slate-400 italic">Belum ada rekaman laporan insiden kedisiplinan yang terdata.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($incidents as $log): ?>
                                <?php 
                                    $is_owner        = ($log['user_id'] == $user_id_login);
                                    $within_time     = (time() - strtotime($log['created_at']) <= 1800);
                                    $boleh_edit_hapus = $is_bk_admin || ($is_owner && $within_time);
                                ?>
                                <tr class="hover:bg-slate-50/60 transition-colors">
                                    <td class="py-3 px-3 font-semibold text-slate-800">
                                        <div><?php echo htmlspecialchars($log['nama_siswa'], ENT_QUOTES, 'UTF-8'); ?></div>
                                        <div class="text-[10px] text-slate-400 font-normal"><?php echo htmlspecialchars($log['nama_kelas'] ?? 'Tanpa Kelas', ENT_QUOTES, 'UTF-8'); ?></div>
                                    </td>
                                    <td class="py-3 px-3">
                                        <div class="font-medium text-slate-700"><?php echo htmlspecialchars($log['nama_kejadian'], ENT_QUOTES, 'UTF-8'); ?></div>
                                        <div class="text-[10px] text-slate-400 max-w-xs truncate" title="<?php echo htmlspecialchars($log['catatan'], ENT_QUOTES, 'UTF-8'); ?>">
                                            <?php echo htmlspecialchars($log['catatan'], ENT_QUOTES, 'UTF-8'); ?>
                                        </div>
                                        <div class="text-[9px] text-slate-400 font-medium mt-0.5">
                                            📍 <?php echo htmlspecialchars($log['lokasi_kejadian'], ENT_QUOTES, 'UTF-8'); ?> | 📣 Pelapor: <?php echo htmlspecialchars($log['nama_pelapor'] ?? 'Sistem', ENT_QUOTES, 'UTF-8'); ?>
                                        </div>
                                    </td>
                                    <td class="py-3 px-3 text-center">
                                        <?php 
                                        $badgeColor = match($log['bobot_risiko']) {
                                            'berat'  => 'bg-rose-50 text-rose-700 border-rose-100',
                                            'sedang' => 'bg-amber-50 text-amber-700 border-amber-100',
                                            default  => 'bg-emerald-50 text-emerald-700 border-emerald-100'
                                        };
                                        ?>
                                        <span class="text-[9px] font-bold uppercase tracking-wider px-2 py-0.5 border rounded-full <?php echo $badgeColor; ?>">
                                            <?php echo htmlspecialchars($log['bobot_risiko'], ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-3 text-right whitespace-nowrap">
                                        <?php if ($boleh_edit_hapus): ?>
                                            <!-- BUG FIX: onclick → bukaModalEditJurnal() + gunakan tanggal_kejadian bukan created_at -->
                                            <button onclick="bukaModalEditJurnal(
                                                <?php echo (int)$log['id']; ?>,
                                                <?php echo (int)$log['category_id']; ?>,
                                                '<?php echo htmlspecialchars(addslashes($log['catatan']), ENT_QUOTES, 'UTF-8'); ?>',
                                                '<?php echo htmlspecialchars($log['lokasi_kejadian'] ?? '', ENT_QUOTES, 'UTF-8'); ?>',
                                                '<?php echo htmlspecialchars($log['tanggal_kejadian'] ?? date('Y-m-d', strtotime($log['created_at'])), ENT_QUOTES, 'UTF-8'); ?>'
                                            )" 
                                                    class="text-indigo-600 hover:text-indigo-900 text-xs font-semibold mr-2 transition-colors">
                                                Edit
                                            </button>
                                            <a href="/modules/jurnal/destroy.php?id=<?php echo $log['id']; ?>" 
                                               onclick="return confirm('Apakah Anda yakin ingin menghapus catatan insiden jurnal ini?')"
                                               class="text-rose-600 hover:text-rose-900 text-xs font-semibold transition-colors">
                                                Hapus
                                            </a>
                                        <?php else: ?>
                                            <span class="text-[10px] text-slate-400 italic select-none" title="Akses terkunci (Melebihi batas waktu 30 menit / Hak akses terbatas)">🔒 Terkunci</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
