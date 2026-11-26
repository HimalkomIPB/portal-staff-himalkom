<?php

namespace App\Notifications;

use App\Models\Proposal;
use DOMDocument;
use DOMElement;
use DOMNode;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProposalNotification extends Notification
{
    use Queueable;

    public $proposal;

    public $message;

    public $url;

    public $title;

    public function __construct(Proposal $proposal, $title, $message, $url)
    {
        $this->proposal = $proposal;
        $this->title = $title;
        $this->message = $message;
        $this->url = $url;
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $statusLabel = match ($this->proposal->status) {
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak / Revisi',
            default => 'Menunggu Review',
        };

        $details = [
            [
                'label' => 'Program Kerja',
                'value' => $this->proposal->nama_proker ?: $this->proposal->title,
                'bold' => true,
            ],
            [
                'label' => 'Status',
                'value' => $statusLabel,
                'bold' => true,
            ],
        ];

        $sanitizedNotes = $this->sanitizeReviewNotes($this->proposal->review_notes);
        if ($sanitizedNotes !== null) {
            $details[] = [
                'label' => 'Catatan Review',
                'value' => $sanitizedNotes,
                'html' => true,
            ];
        }

        if ($this->proposal->reviewed_at) {
            $details[] = [
                'label' => 'Waktu Review',
                'value' => $this->proposal->reviewed_at
                    ->copy()
                    ->timezone('Asia/Jakarta')
                    ->locale('id')
                    ->translatedFormat('l, j F Y, H.i').' WIB',
            ];
        }

        $actionText = $this->proposal->status === 'approved'
            ? 'Cek Program Kerja'
            : 'Lihat Proposal';

        return (new MailMessage)
            ->subject($this->title.' - Himalkom IPB')
            ->view('emails.proposal-notification', [
                'title' => $this->title,
                'recipientName' => $notifiable->name ?? null,
                'notificationMessage' => $this->message,
                'details' => $details,
                'actionText' => $actionText,
                'url' => $this->url,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'proposal_id' => $this->proposal->id,
            'title' => $this->title,
            'message' => $this->message,
            'url' => $this->url,
            'icon' => 'document-text',
        ];
    }

    private function sanitizeReviewNotes(?string $html): ?string
    {
        if ($html === null || trim($html) === '') {
            return null;
        }

        $plainText = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        if (preg_replace('/[\s\x{00A0}]+/u', '', $plainText) === '') {
            return null;
        }

        $allowedTags = [
            'p', 'br', 'div', 'strong', 'b', 'em', 'i', 'u',
            'del', 's', 'ul', 'ol', 'li', 'blockquote', 'pre', 'a',
        ];
        $dropWithContent = ['script', 'style', 'iframe', 'object', 'embed', 'svg'];

        $previousErrorState = libxml_use_internal_errors(true);
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadHTML(
            '<?xml encoding="UTF-8"><div id="__review_notes_root__">'.$html.'</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previousErrorState);

        $root = $dom->getElementById('__review_notes_root__');
        if (! $root) {
            return nl2br(e(trim($plainText)));
        }

        $this->sanitizeNode($root, $allowedTags, $dropWithContent);

        $result = '';
        foreach ($root->childNodes as $child) {
            $result .= $dom->saveHTML($child);
        }

        $result = trim($result);

        return $result !== '' ? $result : null;
    }

    private function sanitizeNode(DOMNode $parent, array $allowedTags, array $dropWithContent): void
    {
        $children = [];
        foreach ($parent->childNodes as $child) {
            $children[] = $child;
        }

        foreach ($children as $child) {
            if (! $child instanceof DOMElement) {
                continue;
            }

            $tag = strtolower($child->tagName);

            if (in_array($tag, $dropWithContent, true)) {
                $parent->removeChild($child);

                continue;
            }

            $this->sanitizeNode($child, $allowedTags, $dropWithContent);

            if (! in_array($tag, $allowedTags, true)) {
                while ($child->firstChild) {
                    $parent->insertBefore($child->firstChild, $child);
                }
                $parent->removeChild($child);

                continue;
            }

            $safeHref = null;
            if ($tag === 'a' && $child->hasAttribute('href')) {
                $href = trim($child->getAttribute('href'));
                if (preg_match('/^(https?:|mailto:)/i', $href)) {
                    $safeHref = $href;
                }
            }

            while ($child->attributes->length > 0) {
                $child->removeAttribute($child->attributes->item(0)->nodeName);
            }

            if ($tag === 'a' && $safeHref !== null) {
                $child->setAttribute('href', $safeHref);
                $child->setAttribute('target', '_blank');
                $child->setAttribute('rel', 'noopener');
                $child->setAttribute('style', 'color: #0E293B; text-decoration: underline;');
            } elseif ($tag === 'ul' || $tag === 'ol') {
                $child->setAttribute('style', 'margin: 4px 0; padding-left: 20px;');
            } elseif ($tag === 'li') {
                $child->setAttribute('style', 'margin: 2px 0;');
            } elseif ($tag === 'p' || $tag === 'div') {
                $child->setAttribute('style', 'margin: 0 0 6px 0;');
            } elseif ($tag === 'blockquote') {
                $child->setAttribute('style', 'margin: 4px 0; padding-left: 12px; border-left: 3px solid #D6E3E7; color: #4A5D68;');
            } elseif ($tag === 'pre') {
                $child->setAttribute('style', 'margin: 4px 0; padding: 8px 10px; background-color: #F4F7F8; font-family: monospace; font-size: 13px; white-space: pre-wrap;');
            }
        }
    }
}
