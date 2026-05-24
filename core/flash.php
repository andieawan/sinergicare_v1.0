<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Menetapkan pesan flash ke dalam session
 * * @param string $type Jenis notifikasi ('success', 'error', 'warning', 'info')
 * @param string $message Isi pesan yang akan ditampilkan
 * @return void
 */
function setFlash(string $type, string $message): void {
    $_SESSION['flash_notification'] = [
        'type'    => $type,
        'message' => $message
    ];
}

/**
 * Memeriksa apakah ada pesan flash yang tersimpan di session
 * * @return bool
 */
function hasFlash(): bool {
    return isset($_SESSION['flash_notification']);
}

/**
 * Merender komponen alert Tailwind CSS untuk pesan flash jika ada,
 * lalu langsung menghapusnya dari session agar tidak muncul kembali saat halaman dimuat ulang.
 * * @return void
 */
function displayFlash(): void {
    if (!hasFlash()) return;

    $flash = $_SESSION['flash_notification'];
    unset($_SESSION['flash_notification']); // Hapus langsung (one-time display)

    $type    = $flash['type'];
    $message = $flash['message'];

    // Pemetaan warna border, background, dan teks menggunakan Tailwind CSS
    $classes = match($type) {
        'success' => 'bg-emerald-50 border-emerald-400 text-emerald-800',
        'error'   => 'bg-rose-50 border-rose-400 text-rose-800',
        'warning' => 'bg-amber-50 border-amber-400 text-amber-800',
        default   => 'bg-blue-50 border-blue-400 text-blue-800',
    };

    // Pemetaan ikon indikator visual
    $icons = match($type) {
        'success' => '✨',
        'error'   => '⚠️',
        'warning' => '🔔',
        default   => 'ℹ️',
    };

    echo "
    <div class='border-l-4 p-4 mb-4 rounded-r-md shadow-sm {$classes}' role='alert'>
        <div class='flex items-center'>
            <span class='mr-3 text-lg select-none'>{$icons}</span>
            <p class='text-sm font-medium'>{$message}</p>
        </div>
    </div>
    ";
}