<div id="modal_edit_jurnal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-sm hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-200 transform transition-all">
        <div class="flex justify-between items-center border-b border-slate-100 pb-3 mb-4">
            <h3 class="text-sm font-bold text-slate-800 tracking-tight">📝 Ubah Catatan Jurnal Insiden</h3>
            <button type="button" onclick="closeModalEditJurnal()" class="text-slate-400 hover:text-slate-600 text-sm">✕</button>
        </div>
        
        <form action="/modules/jurnal/update.php" method="POST" class="space-y-4">
            <input type="hidden" id="edit_jurnal_id" name="id">
            
            <div>
                <label for="edit_jurnal_category_id" class="block text-xs font-semibold text-slate-600 mb-1">Jenis Pelanggaran Baru</label>
                <select id="edit_jurnal_category_id" name="category_id" required
                        class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-slate-800 focus:outline-none focus:border-indigo-500">
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>">
                            [<?php echo strtoupper($cat['bobot_risiko']); ?>] <?php echo htmlspecialchars($cat['nama_kejadian'], ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="edit_jurnal_tanggal" class="block text-xs font-semibold text-slate-600 mb-1">Tanggal</label>
                    <input type="date" id="edit_jurnal_tanggal" name="tanggal_kejadian" required
                           class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-slate-800 focus:outline-none focus:border-indigo-500">
                </div>
                <div>
                    <label for="edit_jurnal_lokasi" class="block text-xs font-semibold text-slate-600 mb-1">Lokasi TKP</label>
                    <input type="text" id="edit_jurnal_lokasi" name="lokasi_kejadian" required
                           class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-slate-800 focus:outline-none focus:border-indigo-500">
                </div>
            </div>

            <div>
                <label for="edit_jurnal_catatan" class="block text-xs font-semibold text-slate-600 mb-1">Kronologi Perubahan</label>
                <textarea id="edit_jurnal_catatan" name="catatan" rows="3" required
                          class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-slate-800 focus:outline-none focus:border-indigo-500"></textarea>
            </div>

            <div class="flex justify-end space-x-2 pt-2">
                <button type="button" onclick="closeModalEditJurnal()" class="px-4 py-2 text-xs font-semibold text-slate-500 hover:text-slate-700 rounded-xl">Batal</button>
                <button type="submit" class="bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs px-4 py-2 rounded-xl shadow-sm tracking-wide">💾 Perbarui Jurnal</button>
            </div>
        </form>
    </div>
</div>

<script>
function bukaModalEditJurnal(id, categoryId, catatan, lokasi, tanggal) {
    document.getElementById('edit_jurnal_id').value = id;
    document.getElementById('edit_jurnal_category_id').value = categoryId;
    document.getElementById('edit_jurnal_catatan').value = catatan;
    document.getElementById('edit_jurnal_lokasi').value = lokasi;
    document.getElementById('edit_jurnal_tanggal').value = tanggal;
    
    const modal = document.getElementById('modal_edit_jurnal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeModalEditJurnal() {
    const modal = document.getElementById('modal_edit_jurnal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}
</script>