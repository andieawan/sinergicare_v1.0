<div class="space-y-6">
    
    <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
        <h3 class="text-sm font-bold text-slate-800 tracking-tight mb-1">⚠️ Direktori Siswa Status Kritis & Masa Probation</h3>
        <p class="text-xs text-slate-400 mb-4">Daftar siswa yang memerlukan keputusan Waka Kesiswaan terkait penerbitan Surat Peringatan (SP) resmi atau pembatasan khusus.</p>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-700 uppercase font-bold border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-3">Identitas Siswa</th>
                        <th class="py-3 px-3 text-center">Status Radar</th>
                        <th class="py-3 px-3 text-center">Status SP Aktif</th>
                        <th class="py-3 px-3 text-center">Status Probation</th>
                        <th class="py-3 px-3 text-right">Keputusan Waka</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($students_critical)): ?>
                        <tr>
                            <td colspan="5" class="py-6 text-center text-slate-400 italic">Alhamdulillah, tidak ada siswa yang berada dalam status kritis saat ini.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($students_critical as $siswa): ?>
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="py-3 px-3 font-semibold text-slate-800">
                                    <div><?php echo htmlspecialchars($siswa['nama'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div class="text-[10px] text-slate-400 font-normal">Kelas: <?php echo htmlspecialchars($siswa['nama_kelas'] ?? 'Tanpa Kelas', ENT_QUOTES, 'UTF-8'); ?> | NISN: <?php echo htmlspecialchars($siswa['nisn'], ENT_QUOTES, 'UTF-8'); ?></div>
                                </td>
                                <td class="py-3 px-3 text-center">
                                    <span class="text-[9px] font-bold uppercase tracking-wider px-2 py-0.5 border rounded-full <?php echo getStatusBadgeClass($siswa['status_warna']); ?>">
                                        <?php echo htmlspecialchars($siswa['status_warna'], ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-center font-bold">
                                    <?php if ($siswa['status_sp'] !== 'tidak_ada'): ?>
                                        <span class="bg-amber-100 text-amber-800 border border-amber-200 text-[10px] px-2 py-0.5 rounded capitalize">
                                            ⚠️ <?php echo htmlspecialchars(str_replace('_', ' ', $siswa['status_sp']), ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-slate-400 font-normal italic">Tidak Ada</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 px-3 text-center">
                                    <?php if ($siswa['is_probation']): ?>
                                        <div class="text-[10px] font-bold text-rose-600 bg-rose-50 border border-rose-100 px-2 py-0.5 rounded-full inline-block">
                                            🛑 Aktif s/d <?php echo date('d/m/Y', strtotime($siswa['probation_end'])); ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-slate-400 italic">Normal</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 px-3 text-right whitespace-nowrap space-x-1">
                                    <button onclick="bukaModalTerbitSp(<?php echo $siswa['id']; ?>, '<?php echo htmlspecialchars($siswa['nama'], ENT_QUOTES, 'UTF-8'); ?>')" 
                                            class="bg-slate-900 text-white hover:bg-slate-800 text-[10px] font-bold px-2.5 py-1.5 rounded-lg shadow-sm transition-colors">
                                        📜 Terbitkan SP
                                    </button>
                                    
                                    <?php if (!$siswa['is_probation']): ?>
                                        <button onclick="alert('Fitur aktivasi probation ID Siswa: <?php echo $siswa['id']; ?> aktif di Tahap 19')" 
                                                class="bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 text-[10px] font-bold px-2.5 py-1.5 rounded-lg transition-colors">
                                            🛑 Set Probation
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
        <h3 class="text-sm font-bold text-slate-800 tracking-tight mb-1">📜 Dokumen Arsip Surat Peringatan (SP) Resmi</h3>
        <p class="text-xs text-slate-400 mb-4">Daftar rekam jejak formal penandatanganan dan validitas ketetapan surat peringatan siswa.</p>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-700 uppercase font-bold border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-3">Siswa & Kelas</th>
                        <th class="py-3 px-3">Tingkat Surat</th>
                        <th class="py-3 px-3">Alasan / Dasar Pertimbangan SP</th>
                        <th class="py-3 px-3 text-center">Otorisasi Waka</th>
                        <th class="py-3 px-3 text-right">Aksi Dokumen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($sp_records)): ?>
                        <tr>
                            <td colspan="5" class="py-6 text-center text-slate-400 italic">Belum ada dokumen Surat Peringatan formal yang terbit.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($sp_records as $sp): ?>
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="py-3 px-3 font-semibold text-slate-800">
                                    <div><?php echo htmlspecialchars($sp['nama_siswa'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div class="text-[10px] text-slate-400 font-normal"><?php echo htmlspecialchars($sp['nama_kelas'] ?? 'Tanpa Kelas', ENT_QUOTES, 'UTF-8'); ?></div>
                                </td>
                                <td class="py-3 px-3 font-bold uppercase text-indigo-700">
                                    <?php echo htmlspecialchars(str_replace('_', ' ', $sp['tingkat_sp']), ENT_QUOTES, 'UTF-8'); ?>
                                </td>
                                <td class="py-3 px-3 max-w-sm font-medium text-slate-700" title="<?php echo htmlspecialchars($sp['alasan_sp'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php echo htmlspecialchars($sp['alasan_sp'], ENT_QUOTES, 'UTF-8'); ?>
                                    <div class="text-[9px] text-slate-400 font-normal mt-0.5">
                                        Dibuat oleh: 👤
                                        <?php
                                        // BUG FIX #7: Sebelumnya menampilkan $sp['diterbitkan_oleh'] (integer ID),
                                        // sekarang menggunakan $sp['nama_pejabat'] dari hasil JOIN di pages/waka.php
                                        echo htmlspecialchars($sp['nama_pejabat'] ?? 'Tidak diketahui', ENT_QUOTES, 'UTF-8');
                                        ?>
                                    </div>
                                </td>
                                <td class="py-3 px-3 text-center">
                                    <?php if ($sp['is_approved']): ?>
                                        <span class="bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-bold px-2 py-0.5 rounded-full select-none">
                                            ✓ Disetujui
                                        </span>
                                    <?php else: ?>
                                        <a href="/modules/waka/approve_sp.php?id=<?php echo $sp['id']; ?>" 
                                           onclick="return confirm('Apakah Anda menyetujui penerbitan berkas resmi SP ini?')"
                                           class="bg-amber-50 text-amber-700 border border-amber-200 hover:bg-amber-100 text-[10px] font-bold px-2 py-1 rounded-md transition-colors">
                                            ⏳ Butuh Approval
                                        </a>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 px-3 text-right">
                                    <a href="/prints/cetak_sp.php?id=<?php echo $sp['id']; ?>" target="_blank"
                                       class="text-indigo-600 hover:text-indigo-900 font-bold text-xs transition-colors">
                                        🖨️ Cetak SP
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="modal_terbit_sp" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-sm hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-200">
        <h3 class="text-sm font-bold text-slate-800 tracking-tight mb-1">📜 Form Eskalasi Surat Peringatan</h3>
        <p class="text-xs text-slate-400 mb-4">Siswa: <span id="sp_siswa_nama" class="font-bold text-slate-700"></span></p>
        
        <form action="/modules/waka/store_sp.php" method="POST" class="space-y-4">
            <input type="hidden" id="sp_student_id" name="student_id">
            
            <div>
                <label for="tingkat_sp" class="block text-xs font-semibold text-slate-600 mb-1">Tingkatan Surat Peringatan</label>
                <select id="tingkat_sp" name="tingkat_sp" required
                        class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-slate-800 focus:outline-none focus:border-indigo-500">
                    <option value="sp_1">Surat Peringatan 1 (SP 1)</option>
                    <option value="sp_2">Surat Peringatan 2 (SP 2)</option>
                    <option value="sp_3">Surat Peringatan 3 (SP 3)</option>
                </select>
            </div>

            <div>
                <label for="alasan_sp" class="block text-xs font-semibold text-slate-600 mb-1">Alasan Penerbitan SP (Wajib)</label>
                <textarea id="alasan_sp" name="alasan_sp" rows="3" required 
                          placeholder="Tuliskan poin pelanggaran kumulatif atau insiden berat yang menjadi landasan dasar hukum penerbitan SP..."
                          class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-slate-800 focus:outline-none focus:border-indigo-500"></textarea>
            </div>

            <div class="flex justify-end space-x-2 pt-2">
                <button type="button" onclick="closeModalTerbitSp()" class="px-4 py-2 text-xs font-semibold text-slate-500 hover:text-slate-700 rounded-xl">Batal</button>
                <button type="submit" class="bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs px-4 py-2 rounded-xl shadow-sm tracking-wide">🚀 Terbitkan Berkas</button>
            </div>
        </form>
    </div>
</div>

<script>
function bukaModalTerbitSp(id, nama) {
    document.getElementById('sp_student_id').value = id;
    document.getElementById('sp_siswa_nama').innerText = nama;
    document.getElementById('modal_terbit_sp').classList.remove('hidden');
    document.getElementById('modal_terbit_sp').classList.add('flex');
}

function closeModalTerbitSp() {
    document.getElementById('modal_terbit_sp').classList.add('hidden');
    document.getElementById('modal_terbit_sp').classList.remove('flex');
}
</script>