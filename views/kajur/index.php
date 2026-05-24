<div class="space-y-6">
    
    <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
        <h3 class="text-sm font-bold text-slate-800 tracking-tight mb-1">📊 Matriks Kontrol Ketertiban per Kelas</h3>
        <p class="text-xs text-slate-400 mb-4">Ringkasan proporsi warna radar siswa guna mempermudah Ketua Jurusan memetakan kelas yang membutuhkan perhatian khusus.</p>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-700 uppercase font-bold border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Nama Kelas</th>
                        <th class="py-3 px-4 text-center">Total Siswa</th>
                        <th class="py-3 px-4 text-center text-emerald-600">Zona Hijau (Aman)</th>
                        <th class="py-3 px-4 text-center text-amber-600">Zona Kuning (Waspada)</th>
                        <th class="py-3 px-4 text-center text-rose-600">Zona Merah (Bahaya)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    <?php if (empty($rekap_kelas_jurusan)): ?>
                        <tr>
                            <td colspan="5" class="py-4 px-4 text-center text-slate-400 italic">Belum ada data rekapitulasi kelas yang tersedia.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($rekap_kelas_jurusan as $kelas): ?>
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="py-3 px-4 font-bold text-slate-800">
                                    🏫 <?php echo htmlspecialchars($kelas['nama_kelas'], ENT_QUOTES, 'UTF-8'); ?>
                                </td>
                                <td class="py-3 px-4 text-center text-slate-500 font-semibold">
                                    <?php echo (int)$kelas['total_siswa']; ?> Siswa
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <span class="bg-emerald-50 text-emerald-700 px-2 py-0.5 rounded font-bold border border-emerald-100">
                                        <?php echo (int)$kelas['jumlah_hijau']; ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <span class="bg-amber-50 text-amber-700 px-2 py-0.5 rounded font-bold border border-amber-100">
                                        <?php echo (int)$kelas['jumlah_kuning']; ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <span class="bg-rose-50 text-rose-700 px-2 py-0.5 rounded font-bold border border-rose-100">
                                        <?php echo (int)$kelas['jumlah_merah']; ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
        <h3 class="text-sm font-bold text-slate-800 tracking-tight mb-1">🔍 Pengawasan Intensif Siswa Vokasi Berisiko</h3>
        <p class="text-xs text-slate-400 mb-4">Daftar siswa aktif di bawah naungan jurusan yang terdeteksi memiliki hambatan kedisiplinan dan memerlukan pembinaan internal.</p>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-700 uppercase font-bold border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Nama Siswa / NISN</th>
                        <th class="py-3 px-4">Kelas</th>
                        <th class="py-3 px-4 text-center">Kondisi Radar</th>
                        <th class="py-3 px-4 text-center">Eskalasi Kasus</th>
                        <th class="py-3 px-4 text-center">Status SP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($siswa_berisiko_jurusan)): ?>
                        <tr>
                            <td colspan="5" class="py-6 px-4 text-center text-slate-400 italic">Luar biasa! Seluruh siswa di bawah koordinasi jurusan berada di Zona Hijau (Aman).</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($siswa_berisiko_jurusan as $siswa): ?>
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="py-3 px-4 font-semibold text-slate-800">
                                    <div><?php echo htmlspecialchars($siswa['nama'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div class="text-[10px] text-slate-400 font-normal">NISN: <?php echo htmlspecialchars($siswa['nisn'], ENT_QUOTES, 'UTF-8'); ?></div>
                                </td>
                                <td class="py-3 px-4 font-medium text-slate-600">
                                    <?php echo htmlspecialchars($siswa['nama_kelas'], ENT_QUOTES, 'UTF-8'); ?>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <span class="text-[9px] font-bold uppercase tracking-wider px-2 py-0.5 border rounded-full <?php echo getStatusBadgeClass($siswa['status_warna']); ?>">
                                        ⚠️ <?php echo htmlspecialchars($siswa['status_warna'], ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center font-bold text-slate-700 capitalize">
                                    <?php echo htmlspecialchars(str_replace('_', ' ', $siswa['level_eskalasi']), ENT_QUOTES, 'UTF-8'); ?>
                                </td>
                                <td class="py-3 px-4 text-center font-semibold">
                                    <?php if ($siswa['status_sp'] !== 'tidak_ada'): ?>
                                        <span class="bg-rose-50 text-rose-600 border border-rose-100 text-[10px] px-2 py-0.5 rounded capitalize">
                                            <?php echo htmlspecialchars(str_replace('_', ' ', $siswa['status_sp']), ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-slate-400 font-normal italic">Clear</span>
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