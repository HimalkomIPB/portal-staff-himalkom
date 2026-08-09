# Dokumentasi Arsitektur Sistem Portal Staff Himalkom

Dokumen ini berisi rangkuman perubahan arsitektur sistem, khususnya pada bagian **User & Role Management (RBAC)**, yang telah diselesaikan untuk mendukung modul-modul lain (seperti Performance/Evaluasi, Request Center, dll).

> **Terakhir diperbarui:** 9 Agustus 2026
> **Dikerjakan oleh:** Divisi Research & Technology (Modul User, RBAC, & Performance)

---

## 1. Konsep Role-Based Access Control (RBAC) Baru
Sebelumnya, sistem menggunakan pengecekan nama role secara statis (`$user->hasRole('bph')`). Pendekatan ini ditinggalkan karena tidak fleksibel.
Sistem sekarang menggunakan **Spatie Laravel Permission** dengan **Granular Permissions** (Hak akses spesifik).

**Aturan Baru:**
Code tidak lagi mengecek "Siapa rolenya?", melainkan mengecek "**Apakah dia punya izin untuk melakukan ini?**" (`$user->can('performance.evaluate')`). Hal ini membuat penambahan role baru di masa depan menjadi sangat mudah tanpa perlu mengubah source code (*Zero Code Change*).

---

## 2. Daftar Role & Tingkat Akses

Berikut adalah 8 Role yang telah didefinisikan, dipetakan, dan **telah diuji secara manual**:

### 👑 1. Supervisor
- **Fungsi:** Pemilik/Pengelola tertinggi sistem.
- **Hak Akses:** Punya semua akses (Bypass semua *permission*). Bisa mengelola akun user, mengelola sistem, dan konfigurasi.
- **Login ke Panel Admin:** `/superadmin`
- **Catatan Teknis:** Akses ke Filament dikontrol oleh `canAccessPanel()` di `User.php`. Sekarang siapapun yang memiliki role `supervisor` bisa masuk ke `/superadmin` tanpa batasan domain email.

### 👁️ 2. Badan Pengurus Harian (BPH)
- **Fungsi:** Pengawas tingkat tinggi (*High-level viewer*).
- **Hak Akses:**
  - Melihat seluruh data organisasi, laporan, dokumen, dan program kerja di semua departemen.
  - Memiliki halaman khusus `ModView` (`/dashboard/mod-view`) untuk melompat antar departemen tanpa batas.
  - **Keterbatasan:** Tidak berhak mengubah operasional spesifik masing-masing divisi (hanya memantau).

### 🎯 3. Steering Committee (SC)
- **Fungsi:** Pengawas khusus departemen tertentu.
- **Mekanisme:** SC bukanlah sekadar Role biasa. Seorang anggota BPH dapat ditugaskan menjadi SC untuk **satu atau banyak departemen**. Pemetaan ini disimpan dalam tabel pivot `sc_assignments`.
- **Hak Akses:**
  - Dashboard utamanya tetap di departemen BPH/BP.
  - Dapat mengakses `/dashboard/{slug-divisi-yang-diawasi}` dan melihat proker secara read-only.
  - Terdapat tombol **"Kembali ke Dashboard"** saat mengunjungi divisi yang diawasi.
  - Berhak memberi komentar/masukan pada proker.
  - **Berhak memberikan penilaian (evaluasi) bulanan** kepada anggota departemen yang diawasinya.

### 💼 4. Managing Director (MD) & PJS
- **Fungsi:** Kepala dari sebuah departemen. PJS (Penanggung Jawab Sementara) memiliki hak yang **sama persis** dengan MD.
- **Hak Akses:**
  - Punya hak penuh atas departemennya (kelola proker, kelola anggota, dokumen, agenda).
  - **Berhak memberikan penilaian (evaluasi) bulanan** kepada anggota departemennya.
  - Melakukan *approval* internal divisi.
  - **Forbidden (403)** jika mencoba mengakses departemen lain.

### 📝 5. Sekretaris Departemen
- **Fungsi:** Pengurus administrasi.
- **Hak Akses:** Mengelola proposal, LPJ, SPJ, persuratan, dan arsip dokumen di departemennya.
- **Permission:** `archive.view`, `archive.manage`, `document.manage-dept`, `document.submit`

### 💰 6. Bendahara Departemen
- **Fungsi:** Pengurus keuangan.
- **Hak Akses:** Mengelola kas, pemasukan/pengeluaran, dan laporan keuangan departemennya.
- **Permission:** `finance.view`, `finance.manage`, `request.create`

### 👥 7. Anggota (Staff)
- **Fungsi:** Staff divisi biasa.
- **Hak Akses:** Sangat terbatas. Hanya dapat melihat agenda, tugas, informasi divisi, dan **melihat hasil penilaian (rapot) dirinya sendiri**.
- **Permission:** `work-program.view`, `agenda.view`, `member.view`, `performance.view-self`

---

## 3. Database Changes (Perubahan Skema)

### Tabel `sc_assignments`
Tabel pivot baru untuk menangani BPH yang merangkap sebagai SC.
- `user_id` (ID BPH yang ditugaskan sebagai SC)
- `department_id` (ID Departemen yang diawasi)

*Contoh: Azka bisa di-assign sebagai BPH sekaligus SC untuk departemen Creative dan Education secara bersamaan.*

---

## 4. Sistem Autentikasi

### Pembuatan Akun via Filament Admin
- Supervisor membuat akun baru lewat `/superadmin` → Menu **Users**.
- Form menyediakan field: Nama, Email, Role, Departemen Utama, dan SC untuk Departemen (muncul otomatis hanya jika role dipilih **BPH**).
- **Otomatisasi Password:** Password di-generate otomatis dan dikirim ke email user. Di lokal (env `MAIL_MAILER=log`), password tersimpan di `storage/logs/laravel.log`.

### `canAccessPanel()` di `User.php`
```php
public function canAccessPanel(Panel $panel): bool
{
    return $this->hasRole('supervisor') || (str_ends_with($this->email, '@himalkom.com') && $this->hasVerifiedEmail());
}
```
Siapapun yang memegang role `supervisor` bisa login ke Filament `/superadmin`, **berapapun domain emailnya**.

---

## 5. Daftar Permission Lengkap

| Permission | Deskripsi | Role yang Memiliki |
|---|---|---|
| `performance.view-all` | Melihat semua evaluasi | supervisor, bph |
| `performance.evaluate` | Memberikan penilaian | supervisor, bph (SC), managing director, pjs |
| `performance.view-self` | Melihat rapot sendiri | anggota |
| `work-program.manage` | Kelola proker | managing director, pjs |
| `work-program.view` | Lihat proker | semua role |
| `member.manage` | Kelola anggota | managing director, pjs |
| `agenda.manage-dept` | Kelola agenda divisi | managing director, pjs |
| `agenda.view` | Lihat agenda | semua role |
| `document.manage-dept` | Kelola dokumen | managing director, pjs, sekretaris |
| `document.submit` | Ajukan dokumen | sekretaris, bendahara |
| `document.approve-internal`| Approval internal | managing director, pjs |
| `finance.manage` | Kelola keuangan | bendahara |
| `finance.view` | Lihat laporan keuangan | bendahara, bph (via SC) |
| `request.create` | Buat request | managing director, pjs, sekretaris, bendahara |
| `archive.view` | Lihat arsip | sekretaris, supervisor, bph |
| `archive.manage` | Kelola arsip | sekretaris, supervisor |

---

## 6. Panduan Integrasi untuk Tim Developer Lain

> [!IMPORTANT]
> **Baca bagian ini sebelum mulai coding fitur baru!**

### Cara Menggunakan Sistem Permission

**1. Di Route (web.php):**
```php
// Lindungi route dengan permission tertentu
Route::get('/keuangan', [FinanceController::class, 'index'])
    ->middleware('permission:finance.manage');

// Bisa juga pakai middleware group
Route::middleware(['auth', 'permission:document.manage-dept'])->group(function () {
    Route::resource('/dokumen', DocumentController::class);
});
```

**2. Di Controller:**
```php
public function store(Request $request)
{
    // Cek permission secara manual
    if (!auth()->user()->can('work-program.manage')) {
        abort(403, 'Unauthorized');
    }
    // ...
}
```

**3. Di Blade View:**
```blade
{{-- Tampilkan tombol hanya jika user punya izin --}}
@can('finance.manage')
    <button>Tambah Transaksi</button>
@endcan

{{-- Tampilkan berdasarkan role --}}
@hasrole('managing director')
    <div>Section khusus MD</div>
@endhasrole
```

**4. Cek SC untuk Departemen Tertentu:**
```php
// Di DashboardController atau middleware custom
if (!$user->isSCOf($department) && !$user->can('performance.view-all')) {
    abort(403);
}
```

---

## 7. Modul yang Belum Dibangun (Tugas Tim Lain)

Berikut fitur yang permission-nya sudah siap, **tinggal dibangun UI dan logika bisnisnya**:

| Modul | Permission Siap | Dikerjakan oleh Tim |
|---|---|---|
| Agenda / Kalender Divisi | `agenda.manage-dept`, `agenda.view` | Tim Agenda |
| Manajemen Dokumen (Proposal/LPJ/SPJ/Surat) | `document.manage-dept`, `document.submit` | Tim Sekretariat |
| Laporan Keuangan / Kas | `finance.manage`, `finance.view` | Tim Finance |
| Request Center & Approval | `request.create`, `document.approve-internal` | Tim Request |
| **Penilaian Bulanan (Performance)** | `performance.evaluate`, `performance.view-self` | **Tim Research & Technology (Azka)** ← Sedang dikerjakan |

---

> [!TIP]
> **Tips Penting:**
> - Jangan hardcode pengecekan role di controller. Selalu gunakan **permission**.
> - Jika butuh permission baru, tambahkan di `RolePermissionSeeder.php` dan jalankan `php artisan db:seed --class=RolePermissionSeeder`.
> - Untuk tes akses, gunakan `php artisan tinker` dan cek dengan `User::find('id')->can('nama.permission')`.

> [!NOTE]
> **Tentang Modul Performance:**
> Modul Evaluasi Bulanan Anggota (`member_evaluations`) sedang dalam tahap pengembangan aktif oleh Tim Research & Technology. Tabel database dan permission-nya akan segera ditambahkan. Pastikan **tidak ada tabel atau model dengan nama `member_evaluations`** yang dibuat oleh tim lain untuk menghindari konflik.
