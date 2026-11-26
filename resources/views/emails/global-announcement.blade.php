<x-email.layout
    :title="$title . ' - Himalkom IPB'"
    :preheader="$title"
    :headline="$title"
    :greeting="'Halo ' . ($recipientName ?? 'Rekan Himalkom') . ','"
>
    <p style="margin: 0 0 16px 0; font-size: 16px; line-height: 1.65; font-weight: 600; color: #132C3B;">
        {!! nl2br(e($announcementMessage)) !!}
    </p>

    <x-slot:note>
        Terima kasih atas perhatian kamu. Jika ada pertanyaan lebih lanjut, hubungi kami di
        <a href="mailto:himalkom@apps.ipb.ac.id" style="color: #0E293B; font-weight: 600; text-decoration: underline;">himalkom@apps.ipb.ac.id</a>.
    </x-slot:note>
</x-email.layout>
