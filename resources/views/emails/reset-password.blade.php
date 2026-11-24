<x-email.layout
    title="Atur Ulang Kata Sandi - Himalkom IPB"
    preheader="Permintaan pengaturan ulang kata sandi akun HIMALKOM kamu."
    headline="Permintaan atur ulang kata sandi akun HIMALKOM kamu"
    :greeting="'Halo ' . ($user->name ?? 'Rekan Himalkom') . ','"
    :details="[
        ['label' => 'Email', 'value' => $user->email, 'copyable' => true],
        ['label' => 'Berlaku hingga', 'value' => $expiresAt],
    ]"
    action-text="Atur ulang kata sandi"
    :action-url="$resetUrl"
>
    <p style="margin: 0; font-size: 16px; line-height: 1.65; font-weight: 600; color: #132C3B;">
        Kami menerima permintaan untuk mengatur ulang kata sandi akun kamu. Tekan tombol di bawah untuk membuat kata sandi baru sebelum tautan kedaluwarsa.
    </p>

    <x-slot:note>
        Tidak merasa meminta atur ulang kata sandi? Abaikan email ini, atau hubungi kami di
        <a href="mailto:himalkom@apps.ipb.ac.id" style="color: #0E293B; font-weight: 600; text-decoration: underline;">himalkom@apps.ipb.ac.id</a>.
    </x-slot:note>
</x-email.layout>
