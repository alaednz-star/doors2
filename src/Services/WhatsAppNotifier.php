<?php

namespace App\Services;

use App\Core\Database;

/**
 * Builds WhatsApp lead notifications for new quote requests.
 *
 * The destination number is read from the admin setting `notification_whatsapp`
 * (digits only, e.g. 213661234567). When it isn't configured, the notifier is
 * "disabled" and callers simply skip the WhatsApp step — nothing breaks.
 *
 * Today this produces a click-to-send https://wa.me/ link (the customer/admin
 * taps once to deliver, which is free and works on any host). The message body
 * is built by a standalone method so a future WhatsApp Business API integration
 * (true server-side auto-send) can reuse the exact same text without rework.
 */
class WhatsAppNotifier
{
    private const SETTING_KEY = 'notification_whatsapp';

    /** Whether an admin notification number is configured. */
    public function isEnabled(): bool
    {
        return $this->number() !== '';
    }

    /** Destination number, digits only (empty when not configured). */
    public function number(): string
    {
        $raw = $this->setting(self::SETTING_KEY, '');
        return preg_replace('/\D+/', '', (string) $raw);
    }

    /**
     * A ready-to-open https://wa.me/ URL that, when followed, opens WhatsApp
     * with the lead message pre-filled to the admin number. Returns null when
     * no number is configured.
     *
     * @param array $quote   ['reference','customer_name','customer_phone', …]
     * @param array $summary ['Collection','Colour','Usage','Construction','Dimensions']
     */
    public function leadUrl(array $quote, array $summary): ?string
    {
        $number = $this->number();
        if ($number === '') {
            return null;
        }
        $text = $this->buildMessage($quote, $summary);
        return 'https://wa.me/' . $number . '?text=' . rawurlencode($text);
    }

    /**
     * The lead message body. Kept separate (and provider-agnostic) so the same
     * text can be reused by a future Business API sender or SMS gateway.
     */
    public function buildMessage(array $quote, array $summary): string
    {
        $qty = (int) ($quote['quantity'] ?? 1);
        $when = $quote['submitted_at'] ?? date('Y-m-d H:i:s');

        $lines = [
            '🚪 NEW QUOTE REQUEST',
            '',
            'Client: ' . ($quote['customer_name'] ?? '—'),
            'Phone: ' . ($quote['customer_phone'] ?? '—'),
            '',
            'Collection: ' . ($summary['Collection'] ?? '—'),
            'Color: ' . ($summary['Colour'] ?? '—'),
            'Usage: ' . ($summary['Usage'] ?? '—'),
            'Construction: ' . ($summary['Construction'] ?? '—'),
            '',
            'Dimensions: ' . ($summary['Dimensions'] ?? '—'),
            'Quantity: ' . $qty,
            '',
            'Reference: ' . ($quote['reference'] ?? '—'),
            'Date: ' . $when,
        ];

        return implode("\n", $lines);
    }

    private function setting(string $key, string $default): string
    {
        try {
            $stmt = Database::conn()->prepare(
                'SELECT setting_value FROM settings WHERE setting_key = ? LIMIT 1'
            );
            $stmt->execute([$key]);
            $val = $stmt->fetchColumn();
            return ($val === false || $val === null || $val === '') ? $default : (string) $val;
        } catch (\Throwable $e) {
            return $default;
        }
    }
}
