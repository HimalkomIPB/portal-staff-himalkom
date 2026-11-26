<x-email.layout
    :title="$title . ' - Himalkom IPB'"
    :preheader="$title"
    :headline="$title"
    :greeting="'Halo ' . ($recipientName ?? 'Rekan Himalkom') . ','"
    action-text="Cek Program Kerja"
    :action-url="$url"
>
    <p style="margin: 0 0 16px 0; font-size: 16px; line-height: 1.65; font-weight: 600; color: #132C3B;">
        {{ $commentMessage }}
    </p>

    <x-slot:note>
        Terima kasih atas perhatian kamu. Kamu menerima email ini karena tergabung dalam pengelolaan program kerja terkait.
    </x-slot:note>
</x-email.layout>
