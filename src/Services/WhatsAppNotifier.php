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
     * @param array $quote    ['reference','customer_name','customer_phone','submitted_at']
     * @param array $doors    [ ['collection','color','usage','construction','dimensions','quantity'], … ]
     * @param int   $totalQty total number of door units across all lines
     */
    public function leadUrl(array $quote, array $doors, int $totalQty): ?string
    {
        $number = $this->number();
        if ($number === '') {
            return null;
        }
        $text = $this->buildMessage($quote, $doors, $totalQty);
        return 'https://wa.me/' . $number . '?text=' . rawurlencode($text);
    }

    /**
     * The lead message body. Lists EVERY ordered door. Kept provider-agnostic so
     * the same text can be reused by a future Business API sender or SMS gateway.
     */
    public function buildMessage(array $quote, array $doors, int $totalQty): string
    {
        $sep = str_repeat('─', 20);
        $lines = [
            'ADK — Nouvelle Demande de Devis',
            '',
            'CLIENT',
            '',
            'Nom : ' . ($quote['customer_name'] ?? '—'),
            'Téléphone : ' . ($quote['customer_phone'] ?? '—'),
            '',
            $sep,
            '',
            'PROJET',
            '',
            'Nombre total de portes : ' . $totalQty,
        ];

        $i = 0;
        foreach ($doors as $d) {
            $i++;
            $lines[] = '';
            $lines[] = $sep;
            $lines[] = '';
            $lines[] = 'PORTE #' . $i;
            $lines[] = '';
            $lines[] = 'Collection : ' . ($d['collection'] ?? '—');
            $lines[] = 'Couleur : ' . ($d['color'] ?? '—');
            $lines[] = 'Usage : ' . ($d['usage'] ?? '—');
            $lines[] = 'Construction : ' . ($d['construction'] ?? '—');
            $lines[] = 'Dimensions : ' . ($d['dimensions'] ?? '—');
            $lines[] = 'Quantité : ' . (int) ($d['quantity'] ?? 1);
        }

        $lines[] = '';
        $lines[] = $sep;
        $lines[] = '';
        $lines[] = 'RÉCAPITULATIF';
        $lines[] = '';
        $lines[] = 'Total modèles : ' . count($doors);
        $lines[] = 'Total portes : ' . $totalQty;
        $lines[] = '';
        $lines[] = 'Référence : ' . ($quote['reference'] ?? '—');
        $lines[] = 'Date : ' . $this->formatDate($quote['submitted_at'] ?? null);
        $lines[] = '';
        $lines[] = 'Merci de contacter le client dans les meilleurs délais.';

        return implode("\n", $lines);
    }

    /** Format a datetime as "29 Juin 2026 • 15:46" (French month names). */
    private function formatDate(?string $dt): string
    {
        $ts = $dt ? strtotime($dt) : time();
        if ($ts === false) {
            $ts = time();
        }
        $months = [1 => 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
                   'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
        return date('j', $ts) . ' ' . $months[(int) date('n', $ts)] . ' ' . date('Y', $ts)
             . ' • ' . date('H:i', $ts);
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
