<?php

use App\Mail\UserCreatedMail;
use App\Models\Proposal;
use App\Models\User;
use App\Notifications\GlobalAnnouncement;
use App\Notifications\ProposalNotification;
use App\Notifications\WorkProgramCommentNotification;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Mail\Markdown;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

function assertHimalkomEmailChrome(string $html): void
{
    expect($html)
        ->toContain('#0E293B')
        ->toContain('#AFE1EA')
        ->toContain('Himalkom')
        ->toContain('IPB')
        ->toMatch('/images\/(email\/himalkom-logo\.png|himalkom_logo\.webp)/')
        ->toContain('images/email/header-bg.png')
        ->toContain('Himpunan Mahasiswa Ilmu Komputer, IPB University')
        ->toContain('himalkom-ipb.com')
        ->toContain('@himalkomipb')
        ->toContain('Email otomatis, tidak perlu dibalas.');
}

test('verify email notification renders with himalkom template', function () {
    $user = User::factory()->unverified()->create([
        'name' => 'Muhammad',
        'email' => 'muhammad@mahasiswa.ac.id',
    ]);

    $mailMessage = (new VerifyEmail)->toMail($user);
    $html = (string) $mailMessage->render();

    expect($mailMessage->subject)->toBe('Verifikasi Alamat Email - Himalkom IPB');
    assertHimalkomEmailChrome($html);
    expect($html)
        ->toContain('Satu langkah lagi untuk mengaktifkan akun HIMALKOM kamu')
        ->toContain('Halo Muhammad,')
        ->toContain('muhammad@mahasiswa.ac.id')
        ->toContain('Berlaku hingga')
        ->toContain('WIB')
        ->toContain('Verifikasi akun')
        ->toContain('/verify-email/')
        ->toContain('himalkom@apps.ipb.ac.id');
});

test('reset password notification renders with himalkom template', function () {
    $user = User::factory()->create([
        'name' => 'Siti Rahma',
        'email' => 'siti@mahasiswa.ac.id',
    ]);

    $mailMessage = (new ResetPassword('test-reset-token-123'))->toMail($user);
    $html = (string) $mailMessage->render();

    expect($mailMessage->subject)->toBe('Atur Ulang Kata Sandi - Himalkom IPB');
    assertHimalkomEmailChrome($html);
    expect($html)
        ->toContain('Permintaan atur ulang kata sandi akun HIMALKOM kamu')
        ->toContain('Halo Siti Rahma,')
        ->toContain('siti@mahasiswa.ac.id')
        ->toContain('Berlaku hingga')
        ->toContain('WIB')
        ->toContain('Atur ulang kata sandi')
        ->toContain('test-reset-token-123');
});

test('user created mailable renders with himalkom template', function () {
    $mailable = new UserCreatedMail([
        'name' => 'Budi Santoso',
        'email' => 'budi@himalkom.com',
    ], 'SecretPass123!');

    $html = $mailable->render();

    assertHimalkomEmailChrome($html);
    expect($html)
        ->toContain('Akun Portal Staff HIMALKOM kamu telah dibuat')
        ->toContain('Halo Budi Santoso,')
        ->toContain('user-select: none;')
        ->toContain('padding: 15px 0 15px 32px;')
        ->toContain('<span style="display: inline-block; user-select: all; -webkit-user-select: all;">budi@himalkom.com</span></td>')
        ->toContain('<span style="display: inline-block; user-select: all; -webkit-user-select: all;">SecretPass123!</span></td>')
        ->toContain('Masuk ke Portal')
        ->toContain(url('/login'));
});

test('global announcement notification renders with himalkom template', function () {
    $user = User::factory()->create([
        'name' => 'Dewi Lestari',
    ]);

    $notification = new GlobalAnnouncement(
        'Rapat Umum Pengurus',
        "Halo semua,\nMohon hadir tepat waktu pukul 19.00 WIB."
    );

    $mailMessage = $notification->toMail($user);
    $html = (string) $mailMessage->render();

    expect($mailMessage->subject)->toBe('Rapat Umum Pengurus');
    assertHimalkomEmailChrome($html);
    expect($html)
        ->toContain('Rapat Umum Pengurus')
        ->toContain('Halo Dewi Lestari,')
        ->toContain('Mohon hadir tepat waktu pukul 19.00 WIB.');
});

test('work program comment notification renders with himalkom template', function () {
    $user = User::factory()->create([
        'name' => 'Andi Wijaya',
    ]);

    $notification = new WorkProgramCommentNotification(
        'Komentar Baru pada Program Kerja IT Today',
        '"Proposal sudah diperbarui." oleh BPH',
        'https://portal.himalkom-ipb.com/dashboard/dept/workprograms/1'
    );

    $mailMessage = $notification->toMail($user);
    $html = (string) $mailMessage->render();

    expect($mailMessage->subject)->toBe('Komentar Baru pada Program Kerja IT Today');
    assertHimalkomEmailChrome($html);
    expect($html)
        ->toContain('Komentar Baru pada Program Kerja IT Today')
        ->toContain('Halo Andi Wijaya,')
        ->toContain('Proposal sudah diperbarui.')
        ->toContain('Cek Program Kerja')
        ->toContain('https://portal.himalkom-ipb.com/dashboard/dept/workprograms/1');
});

test('proposal notification sends via mail and renders with himalkom template', function () {
    $user = User::factory()->create([
        'name' => 'Rizky Pratama',
    ]);

    $proposal = new Proposal([
        'title' => 'IT Today 2026',
        'nama_proker' => 'IT Today 2026',
        'status' => 'approved',
        'review_notes' => '<div>Proposal disetujui:</div><ul><li>Lanjutkan persiapan</li><li><strong>Unggah LPJ</strong><script>alert(1)</script></li></ul>',
        'reviewed_at' => now(),
    ]);
    $proposal->id = '01TESTPROPOSALID';

    $notification = new ProposalNotification(
        $proposal,
        'Hasil Review Proposal: IT Today 2026',
        'Proposal Anda telah Disetujui oleh BPH.',
        'https://portal.himalkom-ipb.com/dashboard/dept/workprograms/1'
    );

    expect($notification->via($user))->toBe(['database', 'mail']);

    $mailMessage = $notification->toMail($user);
    $html = (string) $mailMessage->render();

    expect($mailMessage->subject)->toBe('Hasil Review Proposal: IT Today 2026 - Himalkom IPB');
    assertHimalkomEmailChrome($html);
    expect($html)
        ->toContain('Hasil Review Proposal: IT Today 2026')
        ->toContain('Halo Rizky Pratama,')
        ->toContain('Proposal Anda telah Disetujui oleh BPH.')
        ->toContain('IT Today 2026')
        ->toContain('Disetujui')
        ->toContain('<li style="margin: 2px 0;">Lanjutkan persiapan</li>')
        ->toContain('<strong>Unggah LPJ</strong>')
        ->not->toContain('&lt;ul&gt;')
        ->not->toContain('<script>')
        ->toContain('Cek Program Kerja')
        ->toContain('https://portal.himalkom-ipb.com/dashboard/dept/workprograms/1');
});

test('proposal notification omits empty trix html review notes', function () {
    $user = User::factory()->create([
        'name' => 'Rizky Pratama',
    ]);

    $proposal = new Proposal([
        'title' => 'Linggis Jawa 2.0',
        'nama_proker' => 'Linggis Jawa 2.0',
        'status' => 'approved',
        'review_notes' => '<ul><li><br></li></ul>',
        'reviewed_at' => now(),
    ]);
    $proposal->id = '01TESTPROPOSALEMPTY';

    $notification = new ProposalNotification(
        $proposal,
        'Hasil Review Proposal: Linggis Jawa 2.0',
        'Proposal Anda telah Disetujui oleh BPH.',
        'https://portal.himalkom-ipb.com/dashboard/dept/workprograms/1'
    );

    $html = (string) $notification->toMail($user)->render();

    expect($html)
        ->not->toContain('Catatan Review')
        ->not->toContain('&lt;ul&gt;');
});

test('fallback laravel markdown mail message inherits himalkom styling', function () {
    $notification = new class extends Notification
    {
        public function toMail(object $notifiable): MailMessage
        {
            return (new MailMessage)
                ->subject('Notifikasi Umum')
                ->greeting('Halo!')
                ->line('Ini adalah pesan notifikasi standar.')
                ->action('Buka Portal', 'https://himalkom-ipb.com');
        }
    };

    $user = User::factory()->create();
    $mailMessage = $notification->toMail($user);

    $html = (string) app(Markdown::class)->render(
        $mailMessage->markdown,
        $mailMessage->data()
    );

    assertHimalkomEmailChrome($html);
    expect($html)
        ->toContain('Ini adalah pesan notifikasi standar.')
        ->toContain('Buka Portal')
        ->toContain('https://himalkom-ipb.com');
});
