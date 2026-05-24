<div id="modal_tambah_konsekuensi" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-sm hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-200 transform transition-all">
        <div class="flex justify-between items-center border-b border-slate-100 pb-3 mb-4">
            <h3 class="text-sm font-bold text-slate-800 tracking-tight">🎯 Berikan Tugas Konsekuensi Pemulihan</h3>
            <button type="button" onclick="closeModalKonsekuensi()" class="text-slate-400 hover:text-slate-600 text-sm">✕</button>
        </div>
        <p class="text-xs text-slate-400 mb-4">Siswa: <span id="konsekuensi_siswa_nama" class="font-bold text-slate-700"></span></p>
        
        <form action="/modules/bk/store_task.php" method="POST" class="space-y-4">
            <input type="hidden" id="konsekuensi_student_id" name="student_id">
            
            <div>
                <label for="deskripsi_tugas" class="block text-xs font-semibold text-slate-600 mb-1">Deskripsi Tugas Konsekuensi</label>
                <textarea id="deskripsi_tugas" name="deskripsi_tugas" rows="3" required 
                          placeholder="Misal: Membantu merapikan buku perpustakaan selama 3 hari berturut-turut saat jam istirahat..."
                          class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-slate-800 focus:outline-none focus:border-indigo-500"></textarea>
            </div>

            <div>
                <!-- BUG FIX: input text bebas → select dropdown staf (FK ke staf_sekolah.id) -->
                <label for="penanggung_jawab" class="block text-xs font-semibold text-slate-600 mb-1">Guru Pendamping / Penanggung Jawab</label>
                <select id="penanggung_jawab" name="penanggung_jawab" required
                        class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-slate-800 focus:outline-none focus:border-indigo-500">
                    <option value="" disabled selected>-- Pilih Guru Pendamping --</option>
                    <?php foreach ($staf_list as $staf): ?>
                        <option value="<?php echo (int)$staf['id']; ?>">
                            <?php echo htmlspecialchars($staf['nama'], ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="flex justify-end space-x-2 pt-2">
                <button type="button" onclick="closeModalKonsekuensi()" class="px-4 py-2 text-xs font-semibold text-slate-500 hover:text-slate-700 rounded-xl">Batal</button>
                <button type="submit" class="bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs px-4 py-2 rounded-xl shadow-sm tracking-wide">🚀 Mandatkan Tugas</button>
            </div>
        </form>
    </div>
</div>

<script>
function bukaModalTambahKonsekuensi(id, nama) {
    document.getElementById('konsekuensi_student_id').value = id;
    document.getElementById('konsekuensi_siswa_nama').innerText = nama;
    
    const modal = document.getElementById('modal_tambah_konsekuensi');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeModalKonsekuensi() {
    const modal = document.getElementById('modal_tambah_konsekuensi');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}
</script>
