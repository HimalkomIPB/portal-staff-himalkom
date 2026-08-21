<?php

/**
 * Property-Based Tests: Slot Content Passthrough
 *
 * Validates: Requirements 5.1, 5.2, 1.4, 6.2
 *
 * Properti 1: Konten Slot Dirender Verbatim
 *   Untuk sembarang string konten yang diberikan sebagai $slot,
 *   output HTML yang dihasilkan oleh sidebar-layout harus mengandung
 *   string konten tersebut tanpa modifikasi.
 *
 * Properti 2: Konten Header Slot Muncul di Output
 *   Untuk sembarang string konten yang diberikan sebagai $header slot,
 *   output HTML yang dihasilkan oleh sidebar-layout harus mengandung
 *   string konten tersebut di dalam elemen header.
 *
 * Properti 3: Judul Halaman Dirender di Tag <title>
 *   Untuk sembarang string judul yang valid, output HTML yang dihasilkan
 *   oleh sidebar-layout harus mengandung string tersebut di dalam tag <title>.
 */

use App\Models\User;

// ---------------------------------------------------------------------------
// Properti 1: Konten Slot Dirender Verbatim
// Validates: Requirement 5.1
// ---------------------------------------------------------------------------

/**
 * **Validates: Requirements 5.1**
 *
 * Properti 1: Untuk sembarang string konten $slot, string tersebut harus
 * muncul verbatim di dalam output HTML.
 */
dataset('slot content strings', [
    'simple text'                     => ['Hello Himalkom'],
    'unique sentinel'                 => ['UNIQUE_SLOT_SENTINEL_XJ9K'],
    'html entities text'              => ['Program Kerja &amp; Evaluasi'],
    'number content'                  => ['42'],
    'sentence'                        => ['Ini adalah konten halaman dashboard.'],
    'html tag content'                => ['<p class="test-slot-para">Paragraf slot</p>'],
    'multiword unique string'         => ['sentinel-propty-one-test-abc123'],
    'unicode text'                    => ['Pengumuman: Rapat Pleno Divisi'],
    'slug-like string'                => ['work-program-index-page'],
    'numeric with words'              => ['Periode 2024-2025'],
]);

test(
    'Properti 1 — konten $slot muncul verbatim di output HTML',
    function (string $slotContent) {
        $user = User::factory()->create();

        $this->actingAs($user);

        $html = view('layouts.sidebar-layout', [
            'slot' => new \Illuminate\Support\HtmlString($slotContent),
        ])->render();

        expect($html)->toContain($slotContent);
    }
)->with('slot content strings');

// ---------------------------------------------------------------------------
// Properti 2: Konten Header Slot Muncul di Output
// Validates: Requirements 5.2, 6.2
// ---------------------------------------------------------------------------

/**
 * **Validates: Requirements 5.2, 6.2**
 *
 * Properti 2: Untuk sembarang string konten $header slot, string tersebut
 * harus muncul di dalam area header di output HTML.
 */
dataset('header slot strings', [
    'simple header text'         => ['Program Kerja'],
    'unique sentinel'            => ['UNIQUE_HEADER_SENTINEL_YZ8M'],
    'page title text'            => ['Performance Evaluation'],
    'breadcrumb style'           => ['Dashboard / Arsip'],
    'short label'                => ['Notifikasi'],
    'sentence header'            => ['Daftar Program Kerja Departemen'],
    'slug-like'                  => ['header-sentinel-test-xyz'],
    'uppercase label'            => ['DOKUMEN ARSIP'],
    'period in text'             => ['Periode 2024/2025'],
    'html in header'             => ['<span>Halaman Profil</span>'],
]);

test(
    'Properti 2 — konten $header slot muncul di output HTML',
    function (string $headerContent) {
        $user = User::factory()->create();

        $this->actingAs($user);

        // Render the layout with the header slot via Blade component rendering
        $html = view('layouts.sidebar-layout', [
            'slot'   => new \Illuminate\Support\HtmlString(''),
            'header' => new \Illuminate\Support\HtmlString($headerContent),
        ])->render();

        expect($html)->toContain($headerContent);
    }
)->with('header slot strings');

// ---------------------------------------------------------------------------
// Properti 3: Judul Halaman Dirender di Tag <title>
// Validates: Requirements 1.4
// ---------------------------------------------------------------------------

/**
 * **Validates: Requirements 1.4**
 *
 * Properti 3: Untuk sembarang string title yang diberikan melalui prop $title,
 * string tersebut harus muncul di dalam tag <title> pada output HTML.
 */
dataset('title strings', [
    'simple title'           => ['Program Kerja'],
    'unique sentinel'        => ['TITLE_SENTINEL_PQ7R'],
    'app specific title'     => ['Portal Himalkom'],
    'page name'              => ['Performance Evaluation'],
    'archive page'           => ['Arsip Dokumen'],
    'profile page'           => ['Profil Saya'],
    'notification page'      => ['Notifikasi'],
    'alphanumeric title'     => ['Dashboard2024'],
    'title with separator'   => ['Program Kerja | Portal Himalkom'],
    'indonesian title'       => ['Beranda Departemen'],
]);

test(
    'Properti 3 — prop $title muncul di dalam tag <title>',
    function (string $title) {
        $user = User::factory()->create();

        $this->actingAs($user);

        $html = view('layouts.sidebar-layout', [
            'slot'  => new \Illuminate\Support\HtmlString(''),
            'title' => $title,
        ])->render();

        expect($html)->toContain("<title>{$title}</title>");
    }
)->with('title strings');

// ---------------------------------------------------------------------------
// Properti 3b: Default title ketika $title tidak disediakan
// Validates: Requirements 1.4
// ---------------------------------------------------------------------------

/**
 * **Validates: Requirements 1.4**
 *
 * Properti 3b: Ketika prop $title tidak disediakan, tag <title> harus
 * mengandung nama aplikasi dari config('app.name').
 */
test('Properti 3b — default title menggunakan nama aplikasi ketika $title tidak disediakan', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $html = view('layouts.sidebar-layout', [
        'slot' => new \Illuminate\Support\HtmlString(''),
    ])->render();

    $appName = config('app.name', 'Portal Himalkom');

    expect($html)->toContain("<title>{$appName}</title>");
});
