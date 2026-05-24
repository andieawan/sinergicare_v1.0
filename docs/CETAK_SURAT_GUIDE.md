```markdown
# 📖 Panduan Cetak Surat Cepat - SinergiCare v1.0

## 🎯 Ringkasan Fitur

Fitur **Cetak Surat Cepat** memungkinkan Admin dan BK untuk mencetak berbagai jenis surat tanpa harus menunggu approval status siswa. Sistem ini menyediakan akses instan dengan pencatatan audit trail lengkap.

---

## 🚀 Cara Menggunakan

### 1️⃣ Akses Halaman Cetak Surat

**Path:** `/pages/cetak_surat.php`

**Akses melalui:**
- Sidebar → "🖨️ Cetak Surat" (hanya untuk Admin & BK)
- Direct URL: `https://domain.com/pages/cetak_surat.php`

---

### 2️⃣ Cari Siswa

```
1. Di kotak "Cari Nama / NISN Siswa", ketik minimal 2 karakter
2. Sistem akan menampilkan hasil pencarian real-time (AJAX)
3. Klik siswa yang dipilih untuk melanjutkan
4. Informasi siswa akan muncul di form
```

**Contoh Pencarian:**
- `Aditya` → mencari nama
- `123456789` → mencari NISN

---

### 3️⃣ Pilih Jenis Surat

Tersedia 4 jenis surat:

| No | Jenis Surat | Deskripsi | Form |
|:--:|---|---|:---:|
| 1️⃣ | ✉️ **Surat Panggilan Orang Tua** | Jadwalkan panggilan dengan tanggal & jam | Isi Tanggal + Jam |
| 2️⃣ | 🚶 **Surat Izin Keluar** | Cetak langsung tanpa form tambahan | Instant |
| 3️⃣ | 📜 **Surat Pernyataan** | Cetak langsung tanpa form tambahan | Instant |
| 4️⃣ | ⚠️ **Surat Peringatan (SP)** | Pilih dari SP yang sudah dibuat | Dropdown Selection |

---

### 4️⃣ Isi Form Cetak (Sesuai Jenis)

#### **✉️ Surat Panggilan Orang Tua**
```
Form:
├─ Tanggal Kehadiran  [Date Picker]
└─ Jam Kehadiran      [Time Picker]

Contoh:
├─ 2026-05-25
└─ 14:00
```

#### **⚠️ Surat Peringatan (SP)**
```
Form:
└─ Pilih Surat Peringatan  [Dropdown]

Sistem akan menampilkan daftar SP yang:
├─ Sudah dibuat untuk siswa
├─ Sudah mendapat approval
└─ Diurutkan dari terbaru
```

#### **🚶 Surat Izin & 📜 Pernyataan**
```
Tidak ada form tambahan - langsung cetak
```

---

### 5️⃣ Klik Tombol "🖨️ Cetak"

```
✅ Sistem akan:
   1. Mencatat log cetak di database (audit trail)
   2. Membuka tab baru dengan preview dokumen
   3. Menampilkan dialog print browser
   4. Menutup modal form
```

---

## 🔐 Kontrol Akses

### Siapa Bisa Mengakses?

| Role | Akses | Catatan |
|:---:|:---:|---|
| 👨‍💼 Super Admin | ✅ | Full access |
| 👨‍💼 Admin | ✅ | Full access |
| 🧑‍🎓 BK | ✅ | Full access |
| 👨‍🏫 Guru | ❌ | Tidak ada menu |
| 🏛️ Waka Kesiswaan | ❌ | Tidak ada menu |
| 📋 Kepala Jurusan | ❌ | Tidak ada menu |

---

## 📝 Pencatatan Log

Setiap cetak surat dicatat dalam tabel `log_cetak_surat` dengan data:

```
┌─────────────────────────┬──────────────────────────┐
│ Field                   │ Contoh Nilai             │
├─────────────────────────┼──────────────────────────┤
│ student_id              │ 123                      │
│ tipe_surat              │ panggilan_ortu           │
│ tanggal_cetak           │ 2026-05-24 10:30:45      │
│ user_id                 │ 45 (Admin yang cetak)    │
│ created_at              │ 2026-05-24 10:30:45      │
└─────────────────────────┴──────────────────────────┘
```

**Tipe Surat yang dicatat:**
- `panggilan_ortu` → Surat Panggilan Orang Tua
- `izin_meninggalkan` → Surat Izin Keluar
- `pernyataan_disiplin` → Surat Pernyataan
- `sp` → Surat Peringatan

---

## 🏗️ Arsitektur File

```
project_root/
├─ pages/
│  └─ cetak_surat.php                      ← Halaman utama
│
├─ modules/cetak_surat/
│  ├─ search_student.php                   ← API: cari siswa
│  ├─ get_sp_list.php                      ← API: ambil list SP
│  └─ log_cetak.php                        ← API: catat log cetak
│
└─ prints/
   ├─ cetak_panggilan.php                  ← Template: Surat Panggilan
   ├─ cetak_meninggalkan.php               ← Template: Surat Izin
   ├─ cetak_pernyataan.php                 ← Template: Surat Pernyataan
   └─ cetak_sp.php                         ← Template: Surat Peringatan
```

---

## 🔄 Request/Response Flow

### 1️⃣ Pencarian Siswa

```
Client Request:
→ GET /modules/cetak_surat/search_student.php?q=aditya

Server Response (JSON):
{
  "status": "success",
  "students": [
    {
      "id": 123,
      "nama": "Aditya Pratama",
      "nisn": "1234567890",
      "nama_kelas": "XI RPL 1"
    }
  ]
}
```

### 2️⃣ Ambil Daftar SP

```
Client Request:
→ GET /modules/cetak_surat/get_sp_list.php?student_id=123

Server Response (JSON):
{
  "status": "success",
  "sp_list": [
    {
      "id": 45,
      "tingkat_sp": "sp1",
      "alasan_sp": "Tidak masuk sekolah 3x...",
      "created_at": "2026-05-20",
      "is_approved": 1
    }
  ]
}
```

### 3️⃣ Catat Log Cetak

```
Client Request:
→ POST /modules/cetak_surat/log_cetak.php
   student_id=123
   tipe_surat=panggilan_ortu
   tanggal=2026-05-25
   jam=14:00

Server Response (JSON):
{
  "status": "success",
  "message": "Log surat tercatat"
}
```

---

## ⚙️ Konfigurasi

### Database (Opsional)

Jika ingin mencatat log cetak, buat tabel berikut:

```sql
CREATE TABLE IF NOT EXISTS log_cetak_surat (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  tipe_surat VARCHAR(50) NOT NULL,
  tanggal_cetak DATETIME NOT NULL,
  user_id INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (student_id) REFERENCES students(id),
  FOREIGN KEY (user_id) REFERENCES users(id),
  INDEX idx_student (student_id),
  INDEX idx_tanggal (tanggal_cetak)
);
```

**Catatan:** Jika table tidak ada, sistem tetap berjalan normal (graceful degradation).

---

## 🐛 Troubleshooting

### ❌ Menu "Cetak Surat" tidak muncul di sidebar

**Solusi:**
- Pastikan user login sebagai Admin atau BK
- Refresh halaman browser
- Clear browser cache

### ❌ Pencarian siswa tidak menampilkan hasil

**Solusi:**
- Pastikan query minimal 2 karakter
- Cek ejaan nama/NISN siswa
- Pastikan siswa sudah terdaftar di database

### ❌ Tombol cetak tidak membuka dokumen

**Solusi:**
- Pastikan pop-up blocker browser dinonaktifkan
- Cek console browser (F12) untuk error
- Pastikan path file print sudah benar

### ❌ Log cetak tidak tercatat

**Solusi:**
- Tabel `log_cetak_surat` mungkin belum dibuat
- Cek permission database user
- Lihat error logs di server

---

## 📊 Fitur Lanjutan (Roadmap)

- [ ] Export log cetak ke Excel
- [ ] Filter log cetak berdasarkan tanggal & user
- [ ] Template custom surat
- [ ] Multi-language support
- [ ] QR Code di surat
- [ ] Digital signature

---

## 📞 Support & Bantuan

Jika ada pertanyaan atau bug report, hubungi:
- Developer: ndikawan69-art
- Repository: [GitHub](https://github.com/andieawan/sinergicare_v1.0)

---

**Last Updated:** 24 Mei 2026  
**Version:** 1.0  
**License:** MIT
```
