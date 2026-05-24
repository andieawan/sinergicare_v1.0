<div class="space-y-6">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <div class="lg:col-span-2 bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
            <h3 class="text-sm font-bold text-slate-800 tracking-tight mb-1">🚨 Siswa Dalam Pantauan Khusus (Radar Aktif)</h3>
            <p class="text-xs text-slate-400 mb-4">Daftar siswa dengan akumulasi risiko sedang (kuning) hingga berat (merah) yang membutuhkan pembinaan.</p>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-slate-700 uppercase font-bold border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-3">Nama / Kelas</th>
                            <th class="py-3 px-3 text-center">Status Radar</th>
                            <th class="py-3 px-3 text-center">Level Eskalasi</th>
                            <th class="py-3 px-3 text-right">Tindakan BK</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (empty($students_attention)): ?>
                            <tr>
                                <td colspan="4" class="py-6 text-center text-slate-400 italic">Saat ini seluruh kondisi siswa terpantau aman di Zona Hijau.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($students_attention as $siswa): ?>
                                <tr class="hover:bg-slate-50/60 transition-colors">
                                    <td class="py-3 px-3 font-semibold text-slate-800">
                                        <div><?php echo htmlspecialchars($siswa['nama'], ENT_QUOTES, 'UTF-8'); ?></div>
                                        <div class="text-[10px] text-slate-400 font-normal">NISN: <?php echo htmlspecialchars($siswa['nisn'], ENT_QUOTES, 'UTF-8'); ?> | Kelas: <?php echo htmlspecialchars($siswa['nama_kelas'] ?? 'Tanpa Kelas', ENT_QUOTES, 'UTF-8'); ?></div>
                                    </td>
                                    <td class="py-3 px-3 text-center">
                                        <?php
                                        // BUG FIX: ikon zona sesuai status, bukan selalu 🔴
                                        $icon_zona = match($siswa['status_warna']) {
                                            'merah'  => '🔴',
                                            'kuning' => '🟡',
                                            default  => '🟢'
                                        };
                                        ?>
                                        <span class="text-[9px] font-bold uppercase tracking-wider px-2 py-0.5 border rounded-full <?php echo getStatusBadgeClass($siswa['status_warna']); ?>">
                                            <?php echo $icon_zona; ?> Zona <?php echo htmlspecialchars($siswa['status_warna'], ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-3 text-center text-slate-700 font-semibold capitalize">
                                        <?php echo htmlspecialchars(str_replace('_', ' ', $siswa['level_eskalasi']), ENT_QUOTES, 'UTF-8'); ?>
                                    </td>
                                    <td class="py-3 px-3 text-right whitespace-nowrap space-x-1.5">
                                        <button onclick="bukaModalTambahKonsekuensi(<?php echo (int)$siswa['id']; ?>, '<?php echo htmlspecialchars(addslashes($siswa['nama']), ENT_QUOTES, 'UTF-8'); ?>')" 
                                                class="bg-slate-100 hover:bg-slate-200 text-slate-700 text-[11px] font-bold px-2.5 py-1.5 rounded-lg transition-colors">
                                            + Konsekuensi
                                        </button>
                                        <button onclick="bukaModalCetakSurat(<?php echo (int)$siswa['id']; ?>, '<?php echo htmlspecialchars(addslashes($siswa['nama']), ENT_QUOTES, 'UTF-8'); ?>')" 
                                                class="bg-indigo-50 border border-indigo-200 text-indigo-600 hover:bg-indigo-100 text-[11px] font-bold px-2.5 py-1.5 rounded-lg transition-colors">
                                            ✉️ Panggilan Ortu
                                        </button>
                                        <button onclick="cetakSuratLangsung(<?php echo (int)$siswa['id']; ?>, 'izin_meninggalkan', '/prints/cetak_meninggalkan.php')" 
                                                class="bg-amber-50 border border-amber-200 text-amber-600 hover:bg-amber-100 text-[11px] font-bold px-2.5 py-1.5 rounded-lg transition-colors">
                                            🚶 Izin Keluar
                                        </button>
                                        <button onclick="cetakSuratLangsung(<?php echo (int)$siswa['id']; ?>, 'pernyataan_disiplin', '/prints/cetak_pernyataan.php')" 
                                                class="bg-rose-50 border border-rose-200 text-rose-600 hover:bg-rose-100 text-[11px] font-bold px-2.5 py-1.5 rounded-lg transition-colors">
                                            📜 Pernyataan
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm h-fit">
            <h3 class="text-sm font-bold text-slate-800 tracking-tight mb-1">📜 Log Cetak Surat Panggilan</h3>
            <p class="text-xs text-slate-400 mb-4">Histori generate dokumen undangan fisik panggilan wali murid.</p>
            
            <div class="space-y-3 max-h-80 overflow-y-auto no-scrollbar">
                <?php if (empty($letter_logs)): ?>
                    <p class="text-xs text-slate-400 italic text-center py-4">Belum ada dokumen surat panggilan yang dicetak.</p>
                <?php else: ?>
                    <?php foreach ($letter_logs as $log): ?>
                        <div class="p-3 bg-slate-50 border border-slate-100 rounded-xl space-y-1">
                            <div class="flex justify-between items-center">
                                <span class="text-xs font-bold text-slate-700 truncate max-w-[140px]"><?php echo htmlspecialchars($log['nama_siswa'], ENT_QUOTES, 'UTF-8'); ?></span>
                                <span class="text-[9px] font-bold bg-indigo-100 text-indigo-700 px-1.5 py-0.5 rounded border border-indigo-200">Terarsip</span>
                            </div>
                            <div class="text-[10px] text-slate-500 font-medium">
                                Jadwal Kehadiran: <?php echo formatTanggalIndo($log['tanggal_surat']); ?> Pukul <?php echo htmlspecialchars(substr($log['jam_surat'], 0, 5), ENT_QUOTES, 'UTF-8'); ?> WIB
                            </div>
                            <div class="text-[9px] text-slate-400 font-normal">
                                Dicetak pada: <?php echo date('d/m/Y H:i', strtotime($log['created_at'])); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
        <h3 class="text-sm font-bold text-slate-800 tracking-tight mb-1">🎯 Pemantauan Tugas Konsekuensi & Pemulihan</h3>
        <p class="text-xs text-slate-400 mb-4">Pengawasan real-time terhadap tugas konsekuensi positif yang diamanatkan kepada siswa.</p>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-700 uppercase font-bold border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-3">Siswa & Kelas</th>
                        <th class="py-3 px-3">Tugas / Konsekuensi Pemulihan</th>
                        <th class="py-3 px-3">Penanggung Jawab</th>
                        <th class="py-3 px-3 text-center">Status Kerja</th>
                        <th class="py-3 px-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($active_consequences)): ?>
                        <tr>
                            <td colspan="5" class="py-6 text-center text-slate-400 italic">Tidak ada tugas konsekuensi aktif yang berjalan saat ini.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($active_consequences as $task): ?>
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="py-3 px-3 font-semibold text-slate-800">
                                    <div><?php echo htmlspecialchars($task['nama_siswa'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div class="text-[10px] text-slate-400 font-normal"><?php echo htmlspecialchars($task['nama_kelas'] ?? 'Tanpa Kelas', ENT_QUOTES, 'UTF-8'); ?></div>
                                </td>
                                <td class="py-3 px-3 max-w-sm text-slate-700 font-medium">
                                    <?php echo htmlspecialchars($task['deskripsi_tugas'], ENT_QUOTES, 'UTF-8'); ?>
                                </td>
                                <td class="py-3 px-3 text-slate-500 font-semibold">
                                    👤 <?php echo htmlspecialchars($task['nama_penanggung_jawab'] ?? 'Tidak Diketahui', ENT_QUOTES, 'UTF-8'); ?>
                                </td>
                                <td class="py-3 px-3 text-center">
                                    <span class="text-[9px] font-bold uppercase bg-amber-50 text-amber-700 border border-amber-200 px-2 py-0.5 rounded-full animate-pulse">
                                        ⚡ <?php echo htmlspecialchars($task['status_tugas'], ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-right">
                                    <a href="/modules/bk/complete_task.php?id=<?php echo (int)$task['id']; ?>" 
                                       onclick="return confirm('Nyatakan tugas konsekuensi ini telah Selesai & Valid?')"
                                       class="bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] font-bold px-2.5 py-1.5 rounded-lg shadow-sm transition-colors">
                                        ✓ Selesai
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

<div id="modal_cetak_surat" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-sm hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-200 transform transition-all">
        <h3 class="text-sm font-bold text-slate-800 tracking-tight mb-1">✉️ Jadwalkan Surat Panggilan</h3>
        <p class="text-xs text-slate-400 mb-4">Siswa: <span id="modal_siswa_nama" class="font-bold text-slate-700"></span></p>
        
        <form id="form_cetak_surat" action="/prints/cetak_panggilan.php" method="GET" target="_blank" class="space-y-4" onsubmit="catatLogSuratAsync(event)">
            <input type="hidden" id="modal_student_id" name="student_id">
            
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="surat_tanggal" class="block text-xs font-semibold text-slate-600 mb-1">Tanggal Kehadiran</label>
                    <input type="date" id="surat_tanggal" name="tanggal" required class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-slate-800 focus:outline-none focus:border-indigo-500">
                </div>
                <div>
                    <label for="surat_jam" class="block text-xs font-semibold text-slate-600 mb-1">Jam Kehadiran</label>
                    <input type="time" id="surat_jam" name="jam" required class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-slate-800 focus:outline-none focus:border-indigo-500">
                </div>
            </div>

            <div class="flex justify-end space-x-2 pt-2">
                <button type="button" onclick="closeModalCetakSurat()" class="px-4 py-2 text-xs font-semibold text-slate-500 hover:text-slate-700 rounded-xl">Batal</button>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs px-4 py-2 rounded-xl shadow-sm tracking-wide">⚙️ Generate & Cetak</button>
            </div>
        </form>
    </div>
</div>

<script>
function bukaModalCetakSurat(id, nama) {
    document.getElementById('modal_student_id').value = id;
    document.getElementById('modal_siswa_nama').innerText = nama;
    document.getElementById('modal_cetak_surat').classList.remove('hidden');
    document.getElementById('modal_cetak_surat').classList.add('flex');
}

function closeModalCetakSurat() {
    document.getElementById('modal_cetak_surat').classList.add('hidden');
    document.getElementById('modal_cetak_surat').classList.remove('flex');
}

function catatLogSuratAsync(e) {
    // ... setup FormData ...

    // Alokasikan fetch menuju endpoint terpusat
    fetch('/modules/bk/log_cetak.php', { method: 'POST', body: formData })
        .then(() => { console.log('Log arsip surat panggilan tercatat.'); });
        
    closeModalCetakSurat();
}

function cetakSuratLangsung(studentId, tipeSurat, basePathSurat) {
    // ... setup FormData ...

    // Alokasikan fetch menuju endpoint terpusat
    fetch('/modules/bk/log_cetak.php', { method: 'POST', body: formData })
        .then(res => {
            if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);
            return res.json();
        })
        .then(data => {
            if(data.status === 'success') {
                window.open(`${basePathSurat}?student_id=${studentId}`, '_blank');
            } else {
                alert(data.message);
            }
        })
        .catch(err => {
            window.open(`${basePathSurat}?student_id=${studentId}`, '_blank');
        });
}

// Fungsi BARU untuk mencetak Surat Izin & Pernyataan tanpa menggunakan Modal
function cetakSuratLangsung(studentId, tipeSurat, basePathSurat) {
    const label = tipeSurat === 'izin_meninggalkan' ? 'Surat Izin Meninggalkan Sekolah' : 'Surat Pernyataan Kedisiplinan';
    if (!confirm(`Generate dan cetak ${label}?`)) return;

    let formData = new FormData();
    formData.append('student_id', studentId);
    formData.append('tipe_surat', tipeSurat);

    fetch('/modules/bk/log_cetak.php', { method: 'POST', body: formData })
        // PERBAIKAN: Periksa res.ok sebelum parsing JSON untuk mengantisipasi kegagalan HTTP/PHP Error
        .then(res => {
            if (!res.ok) {
                throw new Error(`HTTP error! status: ${res.status}`);
            }
            return res.json();
        })
        .then(data => {
            if(data.status === 'success') {
                console.log(`[Log Arsip]: ${label} berhasil dicatat.`);
                // Buka pop-up tab baru untuk cetak dokumen otomatis
                window.open(`${basePathSurat}?student_id=${studentId}`, '_blank');
            } else {
                alert('Gagal mencatat log surat: ' + data.message);
            }
        })
        .catch(err => {
            console.error('Error saat mencatat histori surat:', err);
            // Fallback: Tetap izinkan cetak di tab baru meskipun ajax gagal (opsional)
            window.open(`${basePathSurat}?student_id=${studentId}`, '_blank');
        });
}
</script>