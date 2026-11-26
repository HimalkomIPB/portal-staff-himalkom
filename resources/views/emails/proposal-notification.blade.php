<x-email.layout
    :title="$title . ' - Himalkom IPB'"
    :preheader="$notificationMessage"
    :headline="$title"
    :greeting="'Halo ' . ($recipientName ?? 'Rekan Himalkom') . ','"
    :details="$details"
    :action-text="$actionText"
    :action-url="$url"
>
    <p style="margin: 0; font-size: 16px; line-height: 1.65; font-weight: 600; color: #132C3B;">
        {{ $notificationMessage }}
    </p>

    <x-slot:note>
        Terima kasih atas perhatian kamu. Jika ada pertanyaan terkait proposal atau program kerja ini, hubungi kami di
        <a href="mailto:himalkom@apps.ipb.ac.id" style="color: #0E293B; font-weight: 600; text-decoration: underline;">himalkom@apps.ipb.ac.id</a>.
    </x-slot:note>
</x-email.layout>
