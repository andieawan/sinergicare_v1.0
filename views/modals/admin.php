<div id="modal_kelas" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-sm hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-xl border border-slate-200">
        <h3 id="title_modal_kelas" class="text-sm font-bold text-slate-800 tracking-tight border-b border-slate-100 pb-2 mb-4">🏫 Tambah Data Kelas Baru</h3>
        <form id="form_kelas" action="/modules/admin/kelas_store.php" method="POST" class="space-y-4">
            <input type="hidden" id="kelas_id" name="id">
            <div>
                <label for="nama_kelas" class="block text-xs font-semibold text-slate-600 mb-1">Nama Urutan Kelas</label>
                <input type="text" id="nama_kelas" name="nama_kelas" required placeholder="Misal: X DKV 1, XI PPLG 2"
                       class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-slate-800 focus:outline-none focus:border-indigo-500">
            </div>
            <div class="flex justify-end space-x-2 pt-2">
                <button type="button" onclick="closeModalKelas()" class="px-3 py-1.5 text-xs font-semibold text-slate-500 hover:text-slate-700">Batal</button>
                <button type="submit" class="bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs px-4 py-2 rounded-xl shadow-sm">Simpan Data</button>
            </div>
        </form>
    </div>
</div>

<div id="modal_kategori" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-sm hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-200">
        <h3 id="title_modal_kategori" class="text-sm font-bold text-slate-800 tracking-tight border-b border-slate-100 pb-2 mb-4">🔥 Atur Kategori Kejadian</h3>
        <form id="form_kategori" action="/modules/admin/kategori_store.php" method="POST" class="space-y-4">
            <input type="hidden" id="kategori_id" name="id">
            <div>
                <label for="nama_kejadian" class="block text-xs font-semibold text-slate-600 mb-1">Nama Bentuk Kejadian</label>
                <input type="text" id="nama_kejadian" name="nama_kejadian" required placeholder="Misal: Keterlambatan masuk sekolah, Perundungan"
                       class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-slate-800 focus:outline-none focus:border-indigo-500">
            </div>
            <div>
                <label for="bobot_risiko" class="block text-xs font-semibold text-slate-600 mb-1">Bobot Batas Risiko</label>
                <select id="bobot_risiko" name="bobot_risiko" required
                        class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-slate-800 focus:outline-none focus:border-indigo-500">
                    <option value="ringan">Ringan (Teguran Internal)</option>
                    <option value="sedang">Sedang (Intervensi BK)</option>
                    <option value="berat">Berat (Eskalasi Waka/Kritis)</option>
                </select>
            </div>
            <div class="flex justify-end space-x-2 pt-2">
                <button type="button" onclick="closeModalKategori()" class="px-3 py-1.5 text-xs font-semibold text-slate-500 hover:text-slate-700">Batal</button>
                <button type="submit" class="bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs px-4 py-2 rounded-xl shadow-sm">Simpan Aturan</button>
            </div>
        </form>
    </div>
</div>

<div id="modal_staf" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-sm hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-200">
        <h3 id="title_modal_staf" class="text-sm font-bold text-slate-800 tracking-tight border-b border-slate-100 pb-2 mb-4">👤 Otorisasi Akun Pegawai</h3>
        <form id="form_staf" action="/modules/admin/staf_store.php" method="POST" class="space-y-4">
            <input type="hidden" id="staf_id" name="id">
            
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="staf_nama" class="block text-xs font-semibold text-slate-600 mb-1">Nama Lengkap & Gelar</label>
                    <input type="text" id="staf_nama" name="nama" required class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-slate-800 focus:outline-none focus:border-indigo-500">
                </div>
                <div>
                    <label for="staf_email" class="block text-xs font-semibold text-slate-600 mb-1">Email Resmi</label>
                    <input type="email" id="staf_email" name="email" required class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-slate-800 focus:outline-none focus:border-indigo-500">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="staf_username" class="block text-xs font-semibold text-slate-600 mb-1">Username Login</label>
                    <input type="text" id="staf_username" name="username" required class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-slate-800 focus:outline-none focus:border-indigo-500">
                </div>
                <div>
                    <label for="staf_roles" class="block text-xs font-semibold text-slate-600 mb-1">Hak Akses (Role)</label>
                    <select id="staf_roles" name="roles" required class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-slate-800 focus:outline-none focus:border-indigo-500">
                        <option value="guru">Guru Mata Pelajaran</option>
                        <option value="bk">Guru Bimbingan Konseling (BK)</option>
                        <option value="kepala_jurusan">Kepala / Ketua Jurusan</option>
                        <option value="waka_kesiswaan">Waka Kesiswaan</option>
                        <option value="admin">Administrator Sistem</option>
                    </select>
                </div>
            </div>

            <div>
                <label for="staf_password" class="block text-xs font-semibold text-slate-600 mb-1">Password Keamanan</label>
                <input type="password" id="staf_password" name="password" placeholder="Isi password..."
                       class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-slate-800 focus:outline-none focus:border-indigo-500">
                <p id="password_hint" class="text-[10px] text-slate-400 mt-1 hidden">*Biarkan kosong jika tidak berniat mengubah password lama.</p>
            </div>

            <div class="flex justify-end space-x-2 pt-2">
                <button type="button" onclick="closeModalStaf()" class="px-3 py-1.5 text-xs font-semibold text-slate-500 hover:text-slate-700">Batal</button>
                <button type="submit" class="bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs px-4 py-2 rounded-xl shadow-sm">Simpan Otorisasi</button>
            </div>
        </form>
    </div>
</div>

<div id="modal_import_siswa" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-sm hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-200">
        <div class="flex justify-between items-center border-b border-slate-100 pb-3 mb-4">
            <h3 class="text-sm font-bold text-slate-800 tracking-tight">📥 Impor Massal Data Siswa</h3>
            <button type="button" onclick="closeModalImportSiswa()" class="text-slate-400 hover:text-slate-600 text-sm">✕</button>
        </div>
        
        <form action="/modules/siswa/import.php" method="POST" enctype="multipart/form-data" class="space-y-4">
            <div class="bg-slate-50 border border-slate-200 rounded-xl p-3 text-[11px] text-slate-600 space-y-1">
                <p class="font-bold text-slate-700">💡 Panduan Standar Dokumen:</p>
                <p>1. Baris pertama wajib berupa nama kolom: <code class="bg-slate-200 px-1 py-0.5 rounded font-mono text-indigo-600">nisn,nama,kelas</code></p>
                <p>2. Simpan spreadsheet Microsoft Excel Anda ke format <strong>.xlsx (Excel)</strong>.</p>
                <p>3. Jika nama kelas baru ditulis pada berkas, sistem otomatis membuatkan kelas tersebut.</p>
            </div>

            <div>
                <label for="file_excel" class="block text-xs font-semibold text-slate-600 mb-1">Pilih Berkas (.xlsx)</label>
                <input type="file" id="file_excel" name="file_excel" accept=".xlsx" required
                       class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-800 focus:outline-none focus:border-indigo-500 file:mr-3 file:py-1 file:px-2 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
            </div>

            <div class="flex justify-end space-x-2 pt-2">
                <button type="button" onclick="closeModalImportSiswa()" class="px-3 py-1.5 text-xs font-semibold text-slate-500 hover:text-slate-700">Batal</button>
                <button type="submit" class="bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs px-4 py-2 rounded-xl shadow-sm tracking-wide">🚀 Mulai Unggah</button>
            </div>
        </form>
    </div>
</div>

<script>
// CONTROLLER MODAL KELAS
function bukaModalTambahKelas() {
    document.getElementById('title_modal_kelas').innerText = "🏫 Tambah Data Kelas Baru";
    document.getElementById('form_kelas').action = "/modules/admin/kelas_store.php";
    document.getElementById('kelas_id').value = "";
    document.getElementById('nama_kelas').value = "";
    toggleModal('modal_kelas', true);
}
function bukaModalEditKelas(id, nama) {
    document.getElementById('title_modal_kelas').innerText = "🏫 Edit Data Urutan Kelas";
    document.getElementById('form_kelas').action = "/modules/admin/kelas_update.php";
    document.getElementById('kelas_id').value = id;
    document.getElementById('nama_kelas').value = nama;
    toggleModal('modal_kelas', true);
}
function closeModalKelas() { toggleModal('modal_kelas', false); }

// CONTROLLER MODAL KATEGORI
function bukaModalTambahKategori() {
    document.getElementById('title_modal_kategori').innerText = "🔥 Atur Kategori Kejadian Baru";
    document.getElementById('form_kategori').action = "/modules/admin/kategori_store.php";
    document.getElementById('kategori_id').value = "";
    document.getElementById('nama_kejadian').value = "";
    toggleModal('modal_kategori', true);
}
function bukaModalEditKategori(id, nama, bobot) {
    document.getElementById('title_modal_kategori').innerText = "🔥 Ubah Regulasi Jenis Kejadian";
    document.getElementById('form_kategori').action = "/modules/admin/kategori_update.php";
    document.getElementById('kategori_id').value = id;
    document.getElementById('nama_kejadian').value = nama;
    document.getElementById('bobot_risiko').value = bobot;
    toggleModal('modal_kategori', true);
}
function closeModalKategori() { toggleModal('modal_kategori', false); }

// CONTROLLER MODAL PENGGUNA STAF
function bukaModalTambahStaf() {
    document.getElementById('title_modal_staf').innerText = "👤 Daftarkan Akun Staf Baru";
    document.getElementById('form_staf').action = "/modules/admin/staf_store.php";
    document.getElementById('staf_id').value = "";
    document.getElementById('staf_nama').value = "";
    document.getElementById('staf_email').value = "";
    document.getElementById('staf_username').value = "";
    document.getElementById('staf_password').required = true;
    document.getElementById('password_hint').classList.add('hidden');
    toggleModal('modal_staf', true);
}
function bukaModalEditStaf(id, nama, email, username, roles) {
    document.getElementById('title_modal_staf').innerText = "👤 Modifikasi Otoritas Akun";
    document.getElementById('form_staf').action = "/modules/admin/staf_update.php";
    document.getElementById('staf_id').value = id;
    document.getElementById('staf_nama').value = nama;
    document.getElementById('staf_email').value = email;
    document.getElementById('staf_username').value = username;
    document.getElementById('staf_roles').value = roles;
    document.getElementById('staf_password').required = false;
    document.getElementById('password_hint').classList.remove('hidden');
    toggleModal('modal_staf', true);
}
function closeModalStaf() { toggleModal('modal_staf', false); }
// CONTROLLER MODAL IMPOR SISWA
function bukaModalImportSiswa() {
    toggleModal('modal_import_siswa', true);
}
function closeModalImportSiswa() {
    toggleModal('modal_import_siswa', false);
}
// UTILITY TOGGLE CLASS
function toggleModal(id, show) {
    const el = document.getElementById(id);
    if (show) { el.classList.remove('hidden'); el.classList.add('flex'); } 
    else { el.classList.add('hidden'); el.classList.remove('flex'); }
}
</script>