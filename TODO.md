# TODO - Seed/Migration & Force Change Password

## Scope
Perbaiki setup database agar menggunakan seed default user `admin/admin`, lalu paksa ganti password saat login pertama.

## Checklist
- [x] Update migration/setup di `database/1setup.php`
  - [x] Tambah kolom `must_change_password` pada tabel `staf_sekolah`
  - [x] Ubah seed user default menjadi `admin` / `admin`
  - [x] Set `must_change_password = 1` untuk user seed default
  - [x] Perbarui output kredensial default pada setup
- [x] Update alur login di `modules/auth/login.php`
  - [x] Jika login sukses dan `must_change_password = 1`, redirect paksa ke `/edit_profile.php`
  - [x] Set notifikasi bahwa user wajib ganti password
- [x] Update proses ubah profil/password di `actions/proses_profile_edit.php`
  - [x] Saat password baru berhasil disimpan, set `must_change_password = 0`
- [x] Validasi cepat konsistensi perubahan lintas file

## Catatan
- Fokus perubahan minimum, tanpa mengubah arsitektur utama aplikasi.
- Kompatibel dengan mekanisme session/flash yang sudah ada.
