<div class="grid grid-cols-1 md:grid-cols-3 gap-5">
    
    <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Kondisi Siswa Aman</p>
            <h3 class="text-3xl font-bold text-slate-800 tracking-tight"><?php echo (int)$count_hijau; ?></h3>
            <span class="text-[10px] font-semibold text-emerald-700 bg-emerald-50 border border-emerald-100 px-2 py-0.5 rounded-full mt-2 inline-block">Zona Hijau</span>
        </div>
        <div class="text-3xl bg-emerald-50 p-3 rounded-xl select-none">🟢</div>
    </div>

    <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Status Perlu Waspada</p>
            <h3 class="text-3xl font-bold text-slate-800 tracking-tight"><?php echo (int)$count_kuning; ?></h3>
            <span class="text-[10px] font-semibold text-amber-700 bg-amber-50 border border-amber-100 px-2 py-0.5 rounded-full mt-2 inline-block">Zona Kuning</span>
        </div>
        <div class="text-3xl bg-amber-50 p-3 rounded-xl select-none">🟡</div>
    </div>

    <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Penanganan Krusial</p>
            <h3 class="text-3xl font-bold text-slate-800 tracking-tight"><?php echo (int)$count_merah; ?></h3>
            <span class="text-[10px] font-semibold text-rose-700 bg-rose-50 border border-rose-100 px-2 py-0.5 rounded-full mt-2 inline-block">Zona Merah</span>
        </div>
        <div class="text-3xl bg-rose-50 p-3 rounded-xl select-none">🔴</div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    
    <div class="lg:col-span-2 bg-white border border-slate-200 rounded-2xl p-6 shadow-sm flex flex-col justify-between">
        <div>
            <h4 class="text-sm font-bold text-slate-800 tracking-tight mb-1">Proporsi Sebaran Radar Karakter</h4>
            <p class="text-xs text-slate-400 mb-4">Grafik pembagian akumulasi status ketertiban siswa aktif.</p>
        </div>
        <div class="w-full max-h-64 flex justify-center">
            <canvas id="chartRadarSinergi" class="max-w-xs"></canvas>
        </div>
    </div>

    <div class="space-y-6">
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
            <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-3">🏫 Top Kelas Rawan Kasus</h4>
            <div class="space-y-3">
                <?php if (empty($peta_kerawanan_kelas)): ?>
                    <p class="text-xs text-slate-400">Belum ada rekaman akumulasi data kelas.</p>
                <?php else: ?>
                    <?php foreach ($peta_kerawanan_kelas as $index => $rawan): ?>
                        <div class="flex items-center justify-between border-b border-slate-100 pb-2 last:border-none last:pb-0">
                            <span class="text-xs font-semibold text-slate-600"><?php echo ($index+1) . '. ' . htmlspecialchars($rawan['nama_kelas'], ENT_QUOTES, 'UTF-8'); ?></span>
                            <span class="text-[11px] font-bold px-2 py-0.5 rounded bg-slate-100 text-slate-700"><?php echo $rawan['total_cases']; ?> Insiden</span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
            <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-3">🔥 Pelanggaran Tren Bulan Ini</h4>
            <div class="space-y-3">
                <?php if (empty($tren_pelanggaran)): ?>
                    <p class="text-xs text-slate-400">Belum ada statistik laporan pelanggaran bulan ini.</p>
                <?php else: ?>
                    <?php foreach ($tren_pelanggaran as $tren): ?>
                        <div class="space-y-1">
                            <div class="flex justify-between text-xs font-medium text-slate-600">
                                <span class="truncate pr-2"><?php echo htmlspecialchars($tren['nama_kejadian'], ENT_QUOTES, 'UTF-8'); ?></span>
                                <span class="font-bold text-slate-800"><?php echo $tren['jumlah']; ?>×</span>
                            </div>
                            <div class="w-full bg-slate-100 h-1.5 rounded-full overflow-hidden">
                                <div class="bg-indigo-500 h-1.5 rounded-full" style="width: <?php echo min(($tren['jumlah'] * 10), 100); ?>%"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h4 class="text-sm font-bold text-slate-800 tracking-tight">📝 Jurnal Operasional Insiden</h4>
            <p class="text-xs text-slate-400">Daftar kasus terbaru yang tercatat secara real-time ke dalam sistem database.</p>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs text-slate-600">
            <thead class="bg-slate-50 text-slate-700 uppercase font-bold border-b border-slate-200">
                <tr>
                    <th class="py-3 px-4">Nama Siswa / Kelas</th>
                    <th class="py-3 px-4">Detail Kejadian</th>
                    <th class="py-3 px-4 text-center">Risiko</th>
                    <th class="py-3 px-4">Waktu Pelaporan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php if (empty($log_jurnal_terkini)): ?>
                    <tr>
                        <td colspan="4" class="py-4 px-4 text-center text-slate-400 italic">Tidak ada catatan aktivitas laporan insiden untuk hari ini.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($log_jurnal_terkini as $log): ?>
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3 px-4 font-semibold text-slate-800">
                                <div><?php echo htmlspecialchars($log['nama_siswa'], ENT_QUOTES, 'UTF-8'); ?></div>
                                <div class="text-[10px] text-slate-400 font-normal"><?php echo htmlspecialchars($log['nama_kelas'] ?? 'Tanpa Kelas', ENT_QUOTES, 'UTF-8'); ?></div>
                            </td>
                            <td class="py-3 px-4 max-w-xs truncate" title="<?php echo htmlspecialchars($log['catatan'], ENT_QUOTES, 'UTF-8'); ?>">
                                <span class="font-medium text-slate-700 block"><?php echo htmlspecialchars($log['nama_kejadian'], ENT_QUOTES, 'UTF-8'); ?></span>
                                <span class="text-[10px] text-slate-400"><?php echo htmlspecialchars($log['catatan'], ENT_QUOTES, 'UTF-8'); ?></span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <?php 
                                $badgeColor = match($log['bobot_risiko']) {
                                    'berat'  => 'bg-rose-50 text-rose-700 border-rose-100',
                                    'sedang' => 'bg-amber-50 text-amber-700 border-amber-100',
                                    default  => 'bg-emerald-50 text-emerald-700 border-emerald-100'
                                };
                                ?>
                                <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 border rounded-full <?php echo $badgeColor; ?>">
                                    <?php echo htmlspecialchars($log['bobot_risiko'], ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                            </td>
                            <td class="py-3 px-4 text-slate-400 font-medium">
                                <?php echo date('d M Y, H:i', strtotime($log['created_at'])); ?> WIB
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const ctx = document.getElementById('chartRadarSinergi').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Zona Hijau (Aman)', 'Zona Kuning (Waspada)', 'Zona Merah (Bahaya)'],
            datasets: [{
                data: [<?php echo $count_hijau; ?>, <?php echo $count_kuning; ?>, <?php echo $count_merah; ?>],
                backgroundColor: ['#10b981', '#f59e0b', '#f43f5e'],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11, family: 'Inter' } } }
            },
            cutout: '70%'
        }
    });
});
</script>