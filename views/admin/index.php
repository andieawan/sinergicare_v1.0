<div class="bg-slate-900 p-6 rounded-2xl text-white shadow-sm mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
        <h2 class="text-base font-bold tracking-tight md:text-lg">⚙️ Pusat Kontrol Master Data & Hak Akses</h2>
        <p class="text-xs text-slate-400">Kelola konfigurasi kelas, kategori pelanggaran, tingkat risiko, serta kredensial akun staf sekolah.</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="/modules/siswa/export_template_siswa.php" class="bg-slate-700 hover:bg-slate-600 text-white text-xs font-bold px-4 py-2.5 rounded-xl shadow-sm transition-colors flex items-center gap-2">
            📄 Template Siswa
        </a>
        <button onclick="document.getElementById('modal_import_siswa').classList.remove('hidden')" class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold px-4 py-2.5 rounded-xl shadow-sm transition-colors flex items-center gap-2">
            📥 Import Siswa
        </button>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

    <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm flex flex-col justify-between">
        <div>
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider">🏫 Manajemen Data Kelas</h3>
                <button onclick="bukaModalTambahKelas()" class="text-xs text-indigo-600 font-bold hover:text-indigo-800">+ Tambah</button>
            </div>
            <div class="overflow-x-auto max-h-96 no-scrollbar">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-slate-700 uppercase font-bold sticky top-0">
                        <tr>
                            <th class="py-2 px-3">Nama Kelas</th>
                            <th class="py-2 px-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (empty($kelas_list)): ?>
                            <tr><td colspan="2" class="py-4 text-center text-slate-400 italic">Belum ada kelas terdaftar.</td></tr>
                        <?php else: ?>
                            <?php foreach ($kelas_list as $k): ?>
                                <tr class="hover:bg-slate-50/50">
                                    <td class="py-2.5 px-3 font-semibold text-slate-800"><?php echo htmlspecialchars($k['nama_kelas'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td class="py-2.5 px-3 text-right">
                                        <button onclick="bukaModalEditKelas(<?php echo (int)$k['id']; ?>, '<?php echo htmlspecialchars(addslashes($k['nama_kelas']), ENT_QUOTES, 'UTF-8'); ?>')" 
                                                class="text-indigo-600 hover:text-indigo-900 font-medium">Edit</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm flex flex-col justify-between">
        <div>
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider">🔥 Regulasi Jenis Kejadian</h3>
                <button onclick="bukaModalTambahKategori()" class="text-xs text-indigo-600 font-bold hover:text-indigo-800">+ Tambah</button>
            </div>
            <div class="overflow-x-auto max-h-96 no-scrollbar">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-slate-700 uppercase font-bold sticky top-0">
                        <tr>
                            <th class="py-2 px-3">Bentuk Kasus</th>
                            <th class="py-2 px-3 text-center">Risiko</th>
                            <th class="py-2 px-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (empty($kat_list)): ?>
                            <tr><td colspan="3" class="py-4 text-center text-slate-400 italic">Belum ada kategori kasus.</td></tr>
                        <?php else: ?>
                            <?php foreach ($kat_list as $kat): ?>
                                <tr class="hover:bg-slate-50/50">
                                    <td class="py-2.5 px-3 font-medium text-slate-800 truncate max-w-[150px]" title="<?php echo htmlspecialchars($kat['nama_kejadian'], ENT_QUOTES, 'UTF-8'); ?>">
                                        <?php echo htmlspecialchars($kat['nama_kejadian'], ENT_QUOTES, 'UTF-8'); ?>
                                    </td>
                                    <td class="py-2.5 px-3 text-center">
                                        <?php 
                                        $color = match($kat['bobot_risiko']) {
                                            'berat'  => 'bg-rose-50 text-rose-700 border-rose-100',
                                            'sedang' => 'bg-amber-50 text-amber-700 border-amber-100',
                                            default  => 'bg-emerald-50 text-emerald-700 border-emerald-100'
                                        };
                                        ?>
                                        <span class="text-[9px] font-bold uppercase px-1.5 py-0.5 border rounded <?php echo $color; ?>">
                                            <?php echo htmlspecialchars($kat['bobot_risiko'], ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    </td>
                                    <td class="py-2.5 px-3 text-right">
                                        <button onclick="bukaModalEditKategori(<?php echo (int)$kat['id']; ?>, '<?php echo htmlspecialchars(addslashes($kat['nama_kejadian']), ENT_QUOTES, 'UTF-8'); ?>', '<?php echo htmlspecialchars($kat['bobot_risiko'], ENT_QUOTES, 'UTF-8'); ?>')" 
                                                class="text-indigo-600 hover:text-indigo-900 font-medium">Edit</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm flex flex-col justify-between">
        <div>
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider">👤 Manajemen Akun Staf</h3>
                <div class="flex items-center gap-2">
                    <a href="<?= $web_base ?>/modules/admin/export_template_staf.php" title="Unduh Template Excel Staf" class="text-xs text-slate-500 hover:text-slate-800 font-bold">
                        📄 Template
                    </a>
                    <span class="text-slate-200">|</span>
                    <button onclick="document.getElementById('modal_import_staf').classList.remove('hidden')" title="Import Excel Staf" class="text-xs text-emerald-600 hover:text-emerald-800 font-bold">
                        📥 Import
                    </button>
                    <span class="text-slate-200">|</span>
                    <button onclick="bukaModalTambahStaf()" class="text-xs text-indigo-600 font-bold hover:text-indigo-800">+ Akun Baru</button>
                </div>
            </div>
            <div class="overflow-x-auto max-h-96 no-scrollbar">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-slate-700 uppercase font-bold sticky top-0">
                        <tr>
                            <th class="py-2 px-3">Nama Pegawai</th>
                            <th class="py-2 px-3">Otoritas Role</th>
                            <th class="py-2 px-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (empty($staf_list)): ?>
                            <tr><td colspan="3" class="py-4 text-center text-slate-400 italic">Belum ada akun staf operasional.</td></tr>
                        <?php else: ?>
                            <?php foreach ($staf_list as $s): ?>
                                <tr class="hover:bg-slate-50/50">
                                    <td class="py-2.5 px-3 font-semibold text-slate-800">
                                        <div><?php echo htmlspecialchars($s['nama'], ENT_QUOTES, 'UTF-8'); ?></div>
                                        <div class="text-[10px] text-slate-400 font-normal">@<?php echo htmlspecialchars($s['username'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    </td>
                                    <td class="py-2.5 px-3 text-slate-600 font-medium whitespace-nowrap">
                                        <span class="bg-slate-100 text-slate-700 px-2 py-0.5 rounded text-[10px] border border-slate-200">
                                            <?php echo htmlspecialchars(getRoleLabel($s['roles']), ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    </td>
                                    <td class="py-2.5 px-3 text-right">
                                        <button onclick="bukaModalEditStaf(<?php echo (int)$s['id']; ?>, '<?php echo htmlspecialchars(addslashes($s['nama']), ENT_QUOTES, 'UTF-8'); ?>', '<?php echo htmlspecialchars($s['email'], ENT_QUOTES, 'UTF-8'); ?>', '<?php echo htmlspecialchars($s['username'], ENT_QUOTES, 'UTF-8'); ?>', '<?php echo htmlspecialchars($s['roles'], ENT_QUOTES, 'UTF-8'); ?>')" 
                                                class="text-indigo-600 hover:text-indigo-900 font-medium">Edit</button>
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

