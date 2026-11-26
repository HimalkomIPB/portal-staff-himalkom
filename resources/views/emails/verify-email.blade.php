<x-email.layout
    title="Verifikasi Alamat Email - Himalkom IPB"
    preheader="Satu langkah lagi untuk mengaktifkan akun HIMALKOM kamu."
    headline="Satu langkah lagi untuk mengaktifkan akun HIMALKOM kamu"
    :greeting="'Halo ' . ($user->name ?? 'Rekan Himalkom') . ','"
    :details="[
        ['label' => 'Email', 'value' => $user->email, 'copyable' => true],
        ['label' => 'Berlaku hingga', 'value' => $expiresAt],
    ]"
    action-text="Verifikasi akun"
    :action-url="$verificationUrl"
>
    <p style="margin: 0; font-size: 16px; line-height: 1.65; font-weight: 600; color: #132C3B;">
        Tekan tombol di bawah untuk memverifikasi alamat email kamu sebelum tautan kedaluwarsa.
    </p>

    <x-slot:note>
        Tidak merasa mendaftar? Abaikan email ini, atau hubungi kami di
        <a href="mailto:himalkom@apps.ipb.ac.id" style="color: #0E293B; font-weight: 600; text-decoration: underline;">himalkom@apps.ipb.ac.id</a>.
    </x-slot:note>
</x-email.layout>
