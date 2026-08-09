# Dokumentasi Arsitektur Sistem Portal Staff Himalkom

Dokumen ini berisi rangkuman perubahan arsitektur sistem, khususnya pada bagian **User & Role Management (RBAC)**, yang telah diselesaikan untuk mendukung modul-modul lain (seperti Performance/Evaluasi, Request Center, dll).

---

## 1. Konsep Role-Based Access Control (RBAC) Baru
Sebelumnya, sistem menggunakan pengecekan nama role secara statis (`$user->hasRole('bph')`). Pendekatan ini ditinggalkan karena tidak fleksibel. 
Sistem sekarang menggunakan **Spatie Laravel Permission** dengan **Granular Permissions** (Hak akses spesifik). 

**Aturan Baru:**
Code tidak lagi mengecek "Siapa rolenya?", melainkan mengecek "**Apakah dia punya izin untuk melakukan ini?**" (`$user->can('performance.evaluate')`). Hal ini membuat penambahan role baru di masa depan menjadi sangat mudah tanpa perlu mengubah source code (*Zero Code Change*).

---

## 2. Daftar Role & Tingkat Akses

Berikut adalah 8 Role yang telah didefinisikan dan dipetakan ke dalam sistem:

### 👑 1. Supervisor
- **Fungsi:** Pemilik/Pengelola tertinggi sistem.
- **Hak Akses:** Punya semua akses (Bypass semua *permission*). Bisa mengelola akun user, mengelola sistem, dan konfigurasi.

### 👁️ 2. Badan Pengurus Harian (BPH)
- **Fungsi:** Pengawas tingkat tinggi (*High-level viewer*).
- **Hak Akses:** 
  - Melihat seluruh data organisasi, laporan, dokumen, dan program kerja di semua departemen.
  - Memiliki halaman khusus `ModView` untuk melompat antar departemen tanpa batas.
  - **Keterbatasan:** Tidak berhak mengubah operasional spesifik masing-masing divisi (hanya memantau).

### 🎯 3. Steering Committee (SC)
- **Fungsi:** Pengawas khusus departemen tertentu.
- **Mekanisme Baru:** SC bukanlah sekadar Role biasa. Seorang anggota BPH dapat ditugaskan menjadi SC untuk **satu atau banyak departemen**. Pemetaan ini disimpan dalam tabel pivot khusus (`sc_assignments`).
- **Hak Akses:**
  - Melihat seluruh operasional pada divisi yang diawasinya.
  - Berhak memberi komentar/masukan.
  - **Berhak memberikan penilaian (evaluasi) bulanan** kepada anggota departemen yang diawasinya.

### 💼 4. Managing Director (MD) & PJS
- **Fungsi:** Kepala dari sebuah departemen. PJS (Penanggung Jawab Sementara) memiliki hak yang sama persis dengan MD.
- **Hak Akses:**
  - Punya hak penuh atas departemennya (kelola proker, kelola anggota, dokumen, agenda).
  - **Berhak memberikan penilaian (evaluasi) bulanan** kepada anggota departemennya.
  - Melakukan *approval* internal divisi.

### 📝 5. Sekretaris Departemen
- **Fungsi:** Pengurus administrasi.
- **Hak Akses:** Hanya dapat mengelola proposal, LPJ, SPJ, persuratan, dan arsip dokumen di departemennya.

### 💰 6. Bendahara Departemen
- **Fungsi:** Pengurus keuangan.
- **Hak Akses:** Hanya dapat mengelola kas, uang masuk/keluar, dan laporan keuangan departemennya.

### 👥 7. Anggota (Staff)
- **Fungsi:** Staff divisi biasa.
- **Hak Akses:** Sangat terbatas. Hanya dapat melihat agenda, tugas, informasi divisi, dan **melihat hasil penilaian (rapot) dirinya sendiri**.

---

## 3. Database Changes (Perubahan Skema)

### Tabel `sc_assignments`
Tabel pivot baru untuk menangani BPH yang merangkap sebagai SC.
- `user_id` (ID BPH)
- `department_id` (ID Departemen yang diawasi)

*Dengan tabel ini, Bang Luthfi (misalnya) bisa di-assign sebagai BPH sekaligus SC untuk departemen Creative dan External secara bersamaan.*

---

## 4. Sistem Autentikasi Tambahan
- **Otomatisasi Password:** Saat Supervisor membuat akun baru untuk Staff/MD/BPH via halaman Filament, sistem **tidak meminta input password**. Password akan di-generate otomatis secara acak (kriptografi kuat) dan **dikirim langsung ke email** mahasiswa bersangkutan untuk login pertama kali.

---

> [!TIP]
> **Untuk Tim Developer Berikutnya:**
> Pastikan kalian selalu menggunakan middleware permission di Route kalian! Contoh:
> `Route::get('/evaluasi', [Controller::class, 'index'])->middleware('permission:performance.evaluate');`
