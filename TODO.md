# TODO - Bugfix & Security Hardening (SinergiCare)

## Scope
Perbaikan bug dan celah keamanan prioritas tinggi pada alur autentikasi dan modul jurnal.

## Checklist
- [x] Tambah helper CSRF token di `core/functions.php`
  - [x] `csrf_token()`
  - [x] `csrf_validate($token)`
- [x] Hardening login di `modules/auth/login.php`
  - [x] Regenerasi session ID saat login sukses (anti session fixation)
  - [x] Hapus fallback password plaintext
  - [x] Samarkan pesan error database (hindari info leakage)
- [x] Hardening create jurnal di `modules/jurnal/store.php`
  - [x] Validasi CSRF token
  - [x] Validasi `category_id` benar-benar ada di DB
  - [x] Validasi format tanggal `Y-m-d`
  - [x] Batasi panjang `lokasi_kejadian` dan `catatan`
  - [x] Samarkan pesan error database
- [x] Hardening update jurnal di `modules/jurnal/update.php`
  - [x] Validasi CSRF token
  - [x] Validasi `category_id` benar-benar ada di DB
  - [x] Validasi format tanggal `Y-m-d`
  - [x] Batasi panjang `lokasi_kejadian` dan `catatan`
  - [x] Samarkan pesan error database
- [x] Update form di `views/modals/jurnal.php`
  - [x] Tambah hidden input CSRF token
- [x] Verifikasi cepat konsistensi sintaks file yang diubah

## Catatan
- Fokus pada perubahan minimal namun berdampak tinggi.
- Menjaga kompatibilitas struktur aplikasi yang sudah ada.
