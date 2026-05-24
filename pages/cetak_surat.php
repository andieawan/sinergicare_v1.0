<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/auth.php';
require_once __DIR__ . '/../core/functions.php';

requireLogin();

// Check permission - only admin and BK can access
if (!hasRole(['super_admin', 'admin', 'bk'])) {
    die("<h3>❌ Akses Ditolak</h3>Anda tidak memiliki izin untuk mengakses halaman cetak surat.");
}

// BUG FIX (BUG-08): Menggunakan variabel yang benar untuk layout dan memanggil layout standar
$pageTitle = "Cetak Surat - Panel Administrasi";
require_once __DIR__ . '/../views/layouts/header.php';
require_once __DIR__ . '/../views/layouts/sidebar.php';
require_once __DIR__ . '/../views/layouts/topbar.php';
?>

<div class="p-8">
    <div class="max-w-6xl mx-auto">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-slate-900 mb-2">🖨️ Cetak Surat Cepat</h1>
            <p class="text-slate-600">Cetak berbagai jenis surat tanpa perlu menunggu approval status siswa</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <div class="lg:col-span-2 space-y-6">
                
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
                    <h2 class="text-lg font-bold text-slate-800 mb-4">📋 Pilih Siswa</h2>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Cari Nama / NISN Siswa</label>
                            <input 
                                type="text" 
                                id="search_student" 
                                placeholder="Ketik nama atau NISN siswa..." 
                                class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                autocomplete="off"
                            >
                            <div id="search_results" class="mt-3 space-y-2 max-h-96 overflow-y-auto"></div>
                        </div>
                    </div>
                </div>

                <div id="form_section" class="hidden">
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
                        <h2 class="text-lg font-bold text-slate-800 mb-4">✉️ Pilih Jenis Surat</h2>
                        <div id="student_info" class="mb-4 p-3 bg-indigo-50 border border-indigo-200 rounded-lg">
                            <p class="text-sm font-semibold text-indigo-900">Siswa: <span id="selected_student_name"></span></p>
                            <p class="text-xs text-indigo-700">NISN: <span id="selected_student_nisn"></span> | Kelas: <span id="selected_student_class"></span></p>
                        </div>

                        <div class="grid grid-cols-1 gap-3">
                            <div class="border border-slate-200 rounded-lg p-4 hover:bg-slate-50 cursor-pointer transition-colors" onclick="showLetterForm('panggilan_ortu')">
                                <h3 class="font-bold text-slate-800 mb-1">✉️ Surat Panggilan Orang Tua</h3>
                                <p class="text-xs text-slate-600">Jadwalkan panggilan orang tua dengan tanggal dan jam kehadiran</p>
                            </div>

                            <div class="border border-slate-200 rounded-lg p-4 hover:bg-slate-50 cursor-pointer transition-colors" onclick="showLetterForm('izin_meninggalkan')">
                                <h3 class="font-bold text-slate-800 mb-1">🚶 Surat Izin Meninggalkan Sekolah</h3>
                                <p class="text-xs text-slate-600">Cetak surat izin meninggalkan sekolah secara instan</p>
                            </div>

                            <div class="border border-slate-200 rounded-lg p-4 hover:bg-slate-50 cursor-pointer transition-colors" onclick="showLetterForm('pernyataan_disiplin')">
                                <h3 class="font-bold text-slate-800 mb-1">📜 Surat Pernyataan Kedisiplinan</h3>
                                <p class="text-xs text-slate-600">Cetak surat pernyataan kedisiplinan secara instan</p>
                            </div>

                            <div class="border border-slate-200 rounded-lg p-4 hover:bg-slate-50 cursor-pointer transition-colors" onclick="showLetterForm('sp')">
                                <h3 class="font-bold text-slate-800 mb-1">⚠️ Surat Peringatan (SP)</h3>
                                <p class="text-xs text-slate-600">Cetak surat peringatan yang sudah dibuat di sistem</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 h-fit">
                <h2 class="text-lg font-bold text-slate-800 mb-4">📌 Panduan Penggunaan</h2>
                <div class="space-y-4 text-sm text-slate-700">
                    <div>
                        <p class="font-semibold text-slate-800 mb-1">1. Cari Siswa</p>
                        <p class="text-xs">Ketik nama atau NISN siswa di kotak pencarian</p>
                    </div>
                    <div>
                        <p class="font-semibold text-slate-800 mb-1">2. Pilih Jenis Surat</p>
                        <p class="text-xs">Pilih dari berbagai template surat yang tersedia</p>
                    </div>
                    <div>
                        <p class="font-semibold text-slate-800 mb-1">3. Isi Data Tambahan</p>
                        <p class="text-xs">Isi informasi tambahan yang diperlukan (jika ada)</p>
                    </div>
                    <div>
                        <p class="font-semibold text-slate-800 mb-1">4. Cetak Dokumen</p>
                        <p class="text-xs">Klik tombol cetak untuk membuka dan print dokumen</p>
                    </div>
                </div>
                
                <div class="mt-6 p-3 bg-amber-50 border border-amber-200 rounded-lg">
                    <p class="text-xs font-semibold text-amber-900 mb-1">⚡ Info Penting</p>
                    <p class="text-[11px] text-amber-800">Sistem akan mencatat setiap cetak surat di log arsip untuk keperluan audit</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="modal_cetak" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-sm hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-200 transform transition-all">
        <h3 class="text-lg font-bold text-slate-800 tracking-tight mb-4">📝 Form Cetak Surat</h3>
        
        <form id="form_cetak_detail" class="space-y-4" onsubmit="handlePrintSubmit(event)">
            <input type="hidden" id="form_student_id" name="student_id">
            <input type="hidden" id="form_letter_type" name="letter_type">
            
            <div id="fields_panggilan" class="hidden space-y-3">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Tanggal Kehadiran</label>
                    <input type="date" name="tanggal" class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Jam Kehadiran</label>
                    <input type="time" name="jam" class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                </div>
            </div>

            <div id="fields_sp" class="hidden space-y-3">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Pilih Surat Peringatan</label>
                    <select id="sp_select" class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                        <option value="">-- Pilih SP --</option>
                    </select>
                </div>
            </div>

            <div class="flex justify-end space-x-2 pt-4 border-t border-slate-200">
                <button type="button" onclick="closeLetterForm()" class="px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">Batal</button>
                <button type="submit" class="px-4 py-2 text-sm font-bold bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">🖨️ Cetak</button>
            </div>
        </form>
    </div>
</div>

<script>
    let selectedStudent = null;
    let debounceTimer = null;

    // Search Student dengan debounce
    document.getElementById('search_student').addEventListener('keyup', (e) => {
        clearTimeout(debounceTimer);
        const query = e.target.value.trim();
        
        debounceTimer = setTimeout(() => {
            performSearch(query);
        }, 300); // 300ms debounce
    });

    async function performSearch(query) {
        const resultsDiv = document.getElementById('search_results');
        
        if (query.length < 2) {
            resultsDiv.innerHTML = '';
            return;
        }

        try {
            const response = await fetch('/modules/cetak_surat/search_student.php?q=' + encodeURIComponent(query));
            const data = await response.json();
            
            if (data.status === 'success' && data.students.length > 0) {
                resultsDiv.innerHTML = data.students.map(student => `
                    <div class="p-3 bg-slate-100 rounded-lg border border-slate-200 cursor-pointer hover:bg-slate-200 transition-colors" onclick="selectStudent(${student.id}, '${escapeHtml(student.nama)}', '${student.nisn}', '${escapeHtml(student.nama_kelas)}')">
                        <p class="font-semibold text-slate-800">${escapeHtml(student.nama)}</p>
                        <p class="text-xs text-slate-600">NISN: ${student.nisn} | Kelas: ${escapeHtml(student.nama_kelas)}</p>
                    </div>
                `).join('');
            } else {
                resultsDiv.innerHTML = '<p class="text-sm text-slate-400 text-center py-2">Tidak ada data siswa ditemukan</p>';
            }
        } catch (error) {
            console.error('Error:', error);
            resultsDiv.innerHTML = '<p class="text-sm text-red-500 text-center py-2">Error mencari data</p>';
        }
    }

    // Escape HTML untuk security
    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, m => map[m]);
    }

    // Select Student
    function selectStudent(id, nama, nisn, kelas) {
        selectedStudent = { id, nama, nisn, kelas };
        document.getElementById('search_results').innerHTML = '';
        document.getElementById('search_student').value = nama;
        
        document.getElementById('selected_student_name').textContent = nama;
        document.getElementById('selected_student_nisn').textContent = nisn;
        document.getElementById('selected_student_class').textContent = kelas;
        
        document.getElementById('form_section').classList.remove('hidden');
        document.getElementById('form_student_id').value = id;
    }

    // Show Letter Form
    function showLetterForm(letterType) {
        if (!selectedStudent) {
            alert('Pilih siswa terlebih dahulu');
            return;
        }

        // Reset form
        document.getElementById('form_cetak_detail').reset();
        document.getElementById('form_letter_type').value = letterType;
        
        // Hide all field sections
        document.getElementById('fields_panggilan').classList.add('hidden');
        document.getElementById('fields_sp').classList.add('hidden');

        // Show relevant fields
        if (letterType === 'panggilan_ortu') {
            document.getElementById('fields_panggilan').classList.remove('hidden');
        } else if (letterType === 'sp') {
            document.getElementById('fields_sp').classList.remove('hidden');
            loadSPOptions();
        }

        document.getElementById('modal_cetak').classList.remove('hidden');
        document.getElementById('modal_cetak').classList.add('flex');
    }

    // Close Letter Form
    function closeLetterForm() {
        document.getElementById('modal_cetak').classList.add('hidden');
        document.getElementById('modal_cetak').classList.remove('flex');
    }

    // Load SP Options
    async function loadSPOptions() {
        try {
            const response = await fetch('/modules/cetak_surat/get_sp_list.php?student_id=' + selectedStudent.id);
            const data = await response.json();
            const select = document.getElementById('sp_select');
            
            select.innerHTML = '<option value="">-- Pilih SP --</option>';
            if (data.status === 'success' && data.sp_list.length > 0) {
                select.innerHTML += data.sp_list.map(sp => `
                    <option value="${sp.id}">${escapeHtml(sp.tingkat_sp.toUpperCase())} (${sp.created_at})</option>
                `).join('');
            } else {
                select.innerHTML += '<option disabled>Tidak ada SP untuk siswa ini</option>';
            }
        } catch (error) {
            console.error('Error loading SP:', error);
        }
    }

    // Handle Print Submit
    function handlePrintSubmit(e) {
        e.preventDefault();

        const letterType = document.getElementById('form_letter_type').value;
        const studentId = selectedStudent.id;
        const formData = new FormData(e.target);

        // Log cetak surat
        const logData = new FormData();
        logData.append('student_id', studentId);
        logData.append('tipe_surat', letterType);
        
        if (letterType === 'panggilan_ortu') {
            logData.append('tanggal', formData.get('tanggal'));
            logData.append('jam', formData.get('jam'));
        }

        // Fetch log
        fetch('/modules/cetak_surat/log_cetak.php', { method: 'POST', body: logData })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    // Build print URL
                    let printUrl = '';
                    if (letterType === 'panggilan_ortu') {
                        printUrl = `/prints/cetak_panggilan.php?student_id=${studentId}&tanggal=${formData.get('tanggal')}&jam=${formData.get('jam')}`;
                    } else if (letterType === 'izin_meninggalkan') {
                        printUrl = `/prints/cetak_meninggalkan.php?student_id=${studentId}`;
                    } else if (letterType === 'pernyataan_disiplin') {
                        printUrl = `/prints/cetak_pernyataan.php?student_id=${studentId}`;
                    } else if (letterType === 'sp') {
                        const spId = document.getElementById('sp_select').value;
                        printUrl = `/prints/cetak_sp.php?id=${spId}`;
                    }
                    
                    // Open print
                    window.open(printUrl, '_blank');
                    closeLetterForm();
                    
                    // Show success message
                    alert('✅ Surat berhasil dibuat dan log tercatat');
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(err => {
                console.error('Error:', err);
                alert('Terjadi kesalahan saat mencatat log');
            });
    }

    // Close modal when clicking outside
    document.getElementById('modal_cetak').addEventListener('click', (e) => {
        if (e.target.id === 'modal_cetak') {
            closeLetterForm();
        }
    });
</script>

<?php 
// BUG FIX (BUG-08): Menutup layout dengan benar menggunakan footer bawaan
require_once __DIR__ . '/../views/layouts/footer.php'; 
?>