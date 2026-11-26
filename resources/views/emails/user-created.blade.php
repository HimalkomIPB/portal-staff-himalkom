<x-email.layout
    title="Pemberitahuan Data Akun Himalkom"
    preheader="Akun Portal Staff HIMALKOM kamu telah dibuat."
    headline="Akun Portal Staff HIMALKOM kamu telah dibuat"
    :greeting="'Halo ' . ($name ?? 'Rekan Himalkom') . ','"
    :details="[
        ['label' => 'Email', 'value' => $email, 'copyable' => true],
        ['label' => 'Password', 'value' => $password, 'bold' => true, 'mono' => true, 'copyable' => true],
    ]"
    action-text="Masuk ke Portal"
    :action-url="url('/login')"
>
    <p style="margin: 0; font-size: 16px; line-height: 1.65; font-weight: 600; color: #132C3B;">
        Gunakan kredensial di bawah ini untuk masuk ke Portal Staff HIMALKOM. Demi keamanan akun kamu, segera ganti kata sandi setelah berhasil masuk.
    </p>

    <x-slot:note>
        Mengalami kendala saat masuk? Hubungi kami di
        <a href="mailto:himalkom@apps.ipb.ac.id" style="color: #0E293B; font-weight: 600; text-decoration: underline;">himalkom@apps.ipb.ac.id</a>.
    </x-slot:note>
</x-email.layout>
