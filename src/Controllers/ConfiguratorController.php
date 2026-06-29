<?php

namespace App\Controllers;

use App\Core\Database;
use App\Core\Session;
use App\Middleware\SecurityHeaders;
use App\Services\PricingCalculator;
use App\Services\Mailer;
use App\Services\WhatsAppNotifier;
use App\Services\ConfigValidator;
use App\Core\Logger;
use App\Auth\RateLimiter;

class ConfiguratorController
{
    public function __construct()
    {
        SecurityHeaders::apply();
    }

    public function show(): void
    {
        $db = Database::conn();

        $webBase = '/door-showroom/uploads';

        // Per-colour door PHOTO (project assets) shown in the live preview.
        // Keyed by colour name; falls back to a default door so the preview is
        // always a door shape, never a flat texture.
        $assetDir   = '/door-showroom/assets/images/';
        $colorAsset = [
            'Chêne'  => 'chene.jpg',     'Gris'   => 'gris.jpg',
            'Marron' => 'marron-prestige.jpg', 'Marron Prestige' => 'marron-prestige.jpg',
            'Gris Prestige' => 'gris-prestige.jpg',
            'Scuro'  => 'porte-scuro.jpg', 'Simza' => 'portes-cinza.jpg',
            'Madera' => 'portes-madera.jpg', 'Wengue' => 'portes-madera.jpg',
            'Serya'  => 'portes-seery.jpg',
        ];

        // The SWATCH is the flat colour texture for the small grid tiles. Use
        // the dedicated "Texture Image"; if none, the tile falls back to the
        // colour hex (we don't squeeze the big preview photo into a tile).
        $colorSwatch = function (array $c) use ($webBase): ?string {
            if (!empty($c['texture_filename'])) return $webBase . '/colors/' . $c['texture_filename'];
            return null;
        };
        // The DOOR is the image shown in the live preview: the admin-uploaded
        // colour photo (e.g. a door in a room) when present, else a project
        // door asset so the preview is never empty.
        $colorDoor = function (array $c) use ($webBase, $assetDir, $colorAsset): string {
            if (!empty($c['image_filename']))   return $webBase . '/colors/' . $c['image_filename'];
            return $assetDir . ($colorAsset[$c['name']] ?? 'chene.jpg');
        };

        // ── Collections ──
        $collectionsRaw = $db->query(
            'SELECT id, name, slug, image_filename FROM collections WHERE is_active = 1 ORDER BY display_order ASC, name ASC'
        )->fetchAll();

        // ── Colors (each tied to one collection) ──
        $colorsRaw = $db->query(
            'SELECT id, name, hex, collection_id, image_filename, texture_filename
             FROM colors WHERE is_active = 1 ORDER BY display_order ASC, name ASC'
        )->fetchAll();

        // ── Door usages + construction types (with admin-uploaded images) ──
        $usages = $db->query(
            'SELECT id, name, slug, image_filename FROM door_types WHERE is_active = 1 ORDER BY display_order ASC, name ASC'
        )->fetchAll();
        $usages = array_map(static fn ($u) => [
            'id'   => (int)$u['id'],
            'name' => $u['name'],
            'slug' => $u['slug'],
            'img'  => !empty($u['image_filename']) ? $webBase . '/usages/' . $u['image_filename'] : null,
        ], $usages);

        $constructions = $db->query(
            'SELECT id, name, slug, image_filename FROM construction_types WHERE is_active = 1 ORDER BY display_order ASC, name ASC'
        )->fetchAll();
        $constructions = array_map(static fn ($c) => [
            'id'   => (int)$c['id'],
            'name' => $c['name'],
            'slug' => $c['slug'],
            'img'  => !empty($c['image_filename']) ? $webBase . '/construction/' . $c['image_filename'] : null,
        ], $constructions);

        // ── Availability matrix ──
        $matrix = $db->query(
            'SELECT collection_id, door_type_id, construction_type_id, base_price, is_available
             FROM price_rules WHERE is_active = 1'
        )->fetchAll();

        // Colours for the view: `img` = flat swatch for the grid tile;
        // `door` = door photo for the live preview; `hex` = fallback.
        $colorsData = array_map(function ($c) use ($colorSwatch, $colorDoor) {
            return [
                'id'            => (int)$c['id'],
                'name'          => $c['name'],
                'hex'           => $c['hex'],
                'collection_id' => $c['collection_id'] !== null ? (int)$c['collection_id'] : null,
                'img'           => $colorSwatch($c),
                'door'          => $colorDoor($c),
            ];
        }, $colorsRaw);

        $collectionsData = array_map(static fn ($c) => [
            'id'   => (int)$c['id'],
            'name' => $c['name'],
            'slug' => $c['slug'],
            'img'  => !empty($c['image_filename']) ? $webBase . '/collections/' . $c['image_filename'] : null,
        ], $collectionsRaw);

        // Optional preload from a product page (?product=slug) — locks the colour
        // and collection, then the customer continues at Usage.
        $preColorId = null; $preCollectionId = null;
        $slug = trim($_GET['product'] ?? '');
        if ($slug !== '') {
            $stmt = $db->prepare('SELECT collection_id, color_id FROM products WHERE slug = ? AND is_active = 1 LIMIT 1');
            $stmt->execute([$slug]);
            if ($row = $stmt->fetch()) {
                $preCollectionId = $row['collection_id'] !== null ? (int)$row['collection_id'] : null;
                $preColorId      = $row['color_id'] !== null ? (int)$row['color_id'] : null;
            }
        }

        $token = $this->csrfToken();

        require APP_ROOT . '/src/Views/configurator2.php';
    }

    public function price(): void
    {
        header('Content-Type: application/json');

        $this->verifyCsrf();

        $body = $this->jsonBody();

        $calc   = new PricingCalculator();
        $result = $calc->calculate($body);

        echo json_encode(['success' => true, 'pricing' => $result]);
    }

    public function save(): void
    {
        header('Content-Type: application/json');

        $this->verifyCsrf();

        $body = $this->jsonBody();

        $config = $body['config'] ?? [];
        $name   = trim($body['name'] ?? '');

        if (empty($config)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'No configuration provided.']);
            return;
        }

        $calc   = new PricingCalculator();
        $result = $calc->calculate($config);

        $token = bin2hex(random_bytes(32));

        Database::conn()->prepare(
            'INSERT INTO saved_configurations (token, name, config_json, total_price, currency, ip, expires_at)
             VALUES (?, ?, ?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 30 DAY))'
        )->execute([
            $token,
            $name !== '' ? $name : null,
            json_encode($config),
            $result['total_price'],
            $result['currency'],
            $_SERVER['REMOTE_ADDR'] ?? null,
        ]);

        $url = '/door-showroom/configure?ref=' . $token;

        echo json_encode(['success' => true, 'token' => $token, 'url' => $url, 'pricing' => $result]);
    }

    public function load(): void
    {
        header('Content-Type: application/json');

        $token = trim($_GET['ref'] ?? '');

        if (!preg_match('/^[0-9a-f]{64}$/', $token)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid reference.']);
            return;
        }

        $stmt = Database::conn()->prepare(
            'SELECT * FROM saved_configurations WHERE token = ? AND expires_at > NOW() LIMIT 1'
        );
        $stmt->execute([$token]);
        $row = $stmt->fetch();

        if (!$row) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Configuration not found or expired.']);
            return;
        }

        echo json_encode([
            'success' => true,
            'config'  => json_decode($row['config_json'], true),
            'name'    => $row['name'],
            'pricing' => [
                'total_price' => (float)$row['total_price'],
                'currency'    => $row['currency'],
            ],
        ]);
    }

    /** Dedicated luxury quote page — /door-showroom/quote */
    public function quotePage(): void
    {
        Session::start();
        $db = Database::conn();

        $collections = $db->query('SELECT id, name, slug FROM collections WHERE is_active = 1 ORDER BY display_order, name')->fetchAll();
        $materials   = $db->query('SELECT id, name FROM materials WHERE is_active = 1 ORDER BY display_order, name')->fetchAll();
        $colors      = $db->query('SELECT id, name, hex FROM colors WHERE is_active = 1 ORDER BY display_order, name')->fetchAll();
        $doorTypes   = $db->query('SELECT id, name FROM door_types WHERE is_active = 1 ORDER BY display_order, name')->fetchAll();
        $roomTypes   = $db->query('SELECT id, name FROM room_types WHERE is_active = 1 ORDER BY display_order, name')->fetchAll();
        $features    = $db->query('SELECT id, name, price, price_type FROM optional_features WHERE is_active = 1 ORDER BY display_order, name')->fetchAll();

        $token = $this->csrfToken();

        require APP_ROOT . '/src/Views/quote.php';
    }

    public function quote(): void
    {
        header('Content-Type: application/json');
        $this->verifyCsrf();

        $body   = $this->jsonBody();

        // Accept a list of doors (items[]) or a single config (legacy).
        $items = [];
        if (is_array($body['items'] ?? null) && !empty($body['items'])) {
            $items = $body['items'];
        } elseif (is_array($body['config'] ?? null)) {
            $items = [['config' => $body['config'], 'quantity' => (int)($body['quantity'] ?? 1)]];
        }
        $config = is_array($items[0]['config'] ?? null) ? $items[0]['config'] : [];

        // ── Spam: honeypot (bots fill hidden fields) ──
        if (!empty($body['company_website'])) {
            // Pretend success; store nothing.
            echo json_encode(['success' => true, 'reference' => 'ADK-' . date('Y') . '-00000']);
            return;
        }

        // ── Spam: rate limit by IP ──
        $ip      = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $rlKey   = 'quote:' . $ip;
        $limiter = new RateLimiter();
        if ($limiter->isBlocked($rlKey)) {
            http_response_code(429);
            echo json_encode([
                'success' => false,
                'message' => 'Too many requests. Please wait a few minutes and try again, or reach us on WhatsApp.',
            ]);
            return;
        }

        $name    = trim((string)($body['full_name'] ?? ''));
        $email   = strtolower(trim((string)($body['email'] ?? '')));
        $phone   = trim((string)($body['phone'] ?? ''));
        $company = trim((string)($body['company'] ?? ''));
        $country = trim((string)($body['country'] ?? ''));
        $city    = trim((string)($body['city'] ?? ''));
        $ptype   = trim((string)($body['project_type'] ?? ''));
        $instDate= trim((string)($body['install_date'] ?? ''));
        $notes   = trim((string)($body['notes'] ?? ''));

        $allowedTypes = ['residential', 'commercial', 'hospitality', 'architectural'];

        $errors = [];
        if ($name === '')                    $errors['full_name'] = 'Full name is required.';
        elseif (strlen($name) > 120)         $errors['full_name'] = 'Name is too long.';

        if ($email === '')                   $errors['email'] = 'Email address is required.';
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 180)
                                             $errors['email'] = 'Please enter a valid email address.';

        if ($phone === '')                   $errors['phone'] = 'Phone number is required.';
        elseif (strlen($phone) > 30)         $errors['phone'] = 'Phone number is too long.';

        if ($country === '')                 $errors['country'] = 'Installation country is required.';
        if ($city === '')                    $errors['city'] = 'Installation city is required.';

        // Project type is optional (the configurator's Room step captures intent).
        // Only reject an explicitly-provided value that isn't one of the allowed ones.
        if ($ptype !== '' && !in_array($ptype, $allowedTypes, true)) {
            $ptype = '';
        }

        if ($company !== '' && strlen($company) > 160) $errors['company'] = 'Company name is too long.';
        if ($notes !== '' && strlen($notes) > 3000)    $errors['notes'] = 'Notes must not exceed 3000 characters.';

        $installDate = null;
        if ($instDate !== '') {
            $d = \DateTime::createFromFormat('Y-m-d', $instDate);
            if ($d && $d->format('Y-m-d') === $instDate) {
                $installDate = $instDate;
            }
        }

        // ── Validate every door server-side (dimensions, existence) + price it ──
        $validator   = new ConfigValidator();
        $calc        = new PricingCalculator();
        $lineItems   = [];   // [{config, quantity, unit_price, line_total}]
        $grandTotal  = 0.0;
        $cleanConfig = [];   // first door (kept on the row columns for admin filtering)

        foreach ($items as $i => $item) {
            $itemConfig = is_array($item['config'] ?? null) ? $item['config'] : [];
            $itemQty    = (int)($item['quantity'] ?? 1);
            if ($itemQty < 1 || $itemQty > 9999) $itemQty = 1;

            [$ok, $iErr, $clean] = $validator->validate($itemConfig);
            if (!$ok) {
                $errors['config'] = 'One of your doors is incomplete or invalid. Please review your configuration.';
                break;
            }
            $p         = $calc->calculate($clean);
            $unitPrice = (float)($p['total_price'] ?? 0);
            $lineTotal = round($unitPrice * $itemQty, 2);
            $grandTotal += $lineTotal;
            if ($i === 0) $cleanConfig = $clean;

            $lineItems[] = [
                'config'     => $clean,
                'quantity'   => $itemQty,
                'unit_price' => $unitPrice,
                'line_total' => $lineTotal,
            ];
        }
        if (empty($lineItems) && empty($errors['config'])) {
            $errors['config'] = 'Your configuration is incomplete. Please configure a door first.';
        }

        if (!empty($errors)) {
            // A bad submission counts toward the rate limit (deters scripted abuse).
            $limiter->hit($rlKey);
            http_response_code(422);
            echo json_encode(['success' => false, 'errors' => $errors]);
            return;
        }

        $db = Database::conn();

        // ── Duplicate protection: same email + same door list within 5 min ──
        try {
            $cfgHash = sha1(json_encode($lineItems));
            $dup = $db->prepare(
                "SELECT reference FROM quote_requests
                 WHERE customer_email = ? AND config_hash = ?
                   AND submitted_at > (NOW() - INTERVAL 5 MINUTE)
                 ORDER BY id DESC LIMIT 1"
            );
            $dup->execute([$email, $cfgHash]);
            $existingRef = $dup->fetchColumn();
            if ($existingRef) {
                // Idempotent: return the existing reference instead of a duplicate row.
                echo json_encode([
                    'success'   => true,
                    'reference' => $existingRef,
                    'duplicate' => true,
                    'pricing'   => [
                        'total_price'     => $grandTotal,
                        'total_price_fmt' => $this->money($grandTotal),
                        'currency'        => 'DZD',
                    ],
                ]);
                return;
            }
        } catch (\Throwable $e) {
            // config_hash column may not exist yet — skip dedup, do not block the lead.
            $cfgHash = null;
        }

        // Authoritative server-side total (sum of all priced line items).
        $pricing = [
            'total_price'     => $grandTotal,
            'total_price_fmt' => $this->money($grandTotal),
            'currency'        => 'DZD',
        ];

        $intOrNull = static fn ($v) => isset($v) && $v !== '' && (int)$v > 0 ? (int)$v : null;
        $totalQty  = array_sum(array_column($lineItems, 'quantity'));

        // ── Atomic, race-free persistence inside a transaction, with retry on the
        //    (rare) reference collision so a concurrent submission never loses a lead.
        $reference = null;
        $quoteId   = null;
        $maxTries  = 4;

        for ($try = 1; $try <= $maxTries; $try++) {
            try {
                $db->beginTransaction();

                // Atomic per-year sequence via a row lock on the latest reference.
                $year = date('Y');
                $stmtSeq = $db->prepare(
                    "SELECT reference FROM quote_requests
                     WHERE reference LIKE ? ORDER BY id DESC LIMIT 1 FOR UPDATE"
                );
                $stmtSeq->execute(["ADK-{$year}-%"]);
                $lastRef = (string) ($stmtSeq->fetchColumn() ?: '');
                $lastSeq = 0;
                if ($lastRef !== '' && preg_match('/-(\d+)$/', $lastRef, $m)) {
                    $lastSeq = (int) $m[1];
                }
                $reference = sprintf('ADK-%s-%05d', $year, $lastSeq + 1);

                $hasHashCol = $cfgHash !== null;
                // The full door list is stored in features_json (keeps schema stable);
                // the first door's FKs stay on the row columns for admin filtering.
                $cols = 'reference, customer_name, customer_email, customer_company, customer_country,
                         customer_phone, customer_city, project_type, install_date, quantity, notes,
                         product_id, collection_id, material_id, color_id, door_type_id, room_type_id,
                         width_mm, height_mm, features_json, final_price, currency, status, source, submitted_at'
                      . ($hasHashCol ? ', config_hash' : '');
                $vals = '?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW()' . ($hasHashCol ? ',?' : '');

                $params = [
                    $reference, $name, $email,
                    $company !== '' ? $company : null,
                    $country, $phone, $city, $ptype !== '' ? $ptype : null, $installDate, $totalQty,
                    $notes !== '' ? $notes : null,
                    $intOrNull($cleanConfig['product_id'] ?? null),
                    $intOrNull($cleanConfig['collection_id'] ?? null),
                    $intOrNull($cleanConfig['material_id'] ?? null),
                    $intOrNull($cleanConfig['color_id'] ?? null),
                    $intOrNull($cleanConfig['door_type_id'] ?? null),
                    $intOrNull($cleanConfig['room_type_id'] ?? null),
                    $intOrNull($cleanConfig['width_mm'] ?? null),
                    $intOrNull($cleanConfig['height_mm'] ?? null),
                    json_encode(['items' => $lineItems]),
                    $pricing['total_price'], $pricing['currency'], 'new', 'configurator',
                ];
                if ($hasHashCol) {
                    $params[] = $cfgHash;
                }

                $db->prepare("INSERT INTO quote_requests ({$cols}) VALUES ({$vals})")->execute($params);
                $quoteId = (int) $db->lastInsertId();

                $db->commit();
                break; // success
            } catch (\Throwable $e) {
                if ($db->inTransaction()) {
                    $db->rollBack();
                }
                // Unique-key collision on reference → retry with a fresh sequence.
                $isDup = ($e instanceof \PDOException) && (($e->errorInfo[1] ?? 0) === 1062);
                if ($isDup && $try < $maxTries) {
                    usleep(20000 * $try);
                    continue;
                }
                // Unrecoverable — log everything so the lead is never silently lost.
                Logger::error('quote', 'Failed to persist quote request', [
                    'email' => $email,
                    'try'   => $try,
                    'error' => $e->getMessage(),
                ]);
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'message' => 'We could not submit your request right now. Please try again, or contact us on WhatsApp and quote your configuration.',
                ]);
                return;
            }
        }

        // A successful submission resets this IP's counter.
        $limiter->clear($rlKey);

        // Build a human summary for the notifications + admin.
        $summary = $this->resolveSummary($db, $cleanConfig);

        $quoteRow = [
            'reference'        => $reference,
            'customer_name'    => $name,
            'customer_email'   => $email,
            'customer_phone'   => $phone,
            'customer_company' => $company,
            'customer_country' => $country,
            'customer_city'    => $city,
            'project_type'     => $ptype,
            'quantity'         => $totalQty,
            'install_date'     => $installDate,
            'notes'            => $notes,
            'submitted_at'     => date('Y-m-d H:i:s'),
        ];

        // Notifications: hooks wired; live email send is off until SMTP is set.
        // Every new quote ALWAYS produces an admin alert (logged when not live).
        try {
            $mailer = new Mailer();
            $mailer->customerConfirmation($quoteRow, $pricing, $summary);
            $mailer->adminAlert($quoteRow, $pricing, $summary);
        } catch (\Throwable $e) {
            Logger::warning('quote', 'Notification dispatch failed', [
                'reference' => $reference, 'error' => $e->getMessage(),
            ]);
        }

        // WhatsApp lead notification: a ready-to-open wa.me link to the admin's
        // configured number, pre-filled with the order. Null when no number is
        // set in Settings → Notifications — the front-end simply skips it.
        $whatsappUrl = null;
        try {
            $whatsappUrl = (new WhatsAppNotifier())->leadUrl($quoteRow, $summary);
        } catch (\Throwable $e) {
            Logger::warning('quote', 'WhatsApp notification build failed', [
                'reference' => $reference, 'error' => $e->getMessage(),
            ]);
        }

        echo json_encode([
            'success'      => true,
            'reference'    => $reference,
            'quote_id'     => $quoteId,
            'total_qty'    => $totalQty,
            'whatsapp_url' => $whatsappUrl,
            'pricing'      => [
                'total_price'     => $pricing['total_price'],
                'total_price_fmt' => $pricing['total_price_fmt'] ?? null,
                'currency'        => $pricing['currency'],
            ],
        ]);
    }

    private function money(float $amount): string
    {
        return number_format($amount, 0, '.', ' ') . ' DZD';
    }

    /** Resolve config FK ids to human labels for emails/summary. */
    private function resolveSummary(\PDO $db, array $config): array
    {
        $label = static function (string $table, $id) use ($db): string {
            if (!$id) return '—';
            $stmt = $db->prepare("SELECT name FROM {$table} WHERE id = ? LIMIT 1");
            $stmt->execute([(int)$id]);
            return (string)($stmt->fetchColumn() ?: '—');
        };

        $w = (int)($config['width_mm'] ?? 0);
        $h = (int)($config['height_mm'] ?? 0);

        return [
            'Collection'   => $label('collections',        $config['collection_id'] ?? null),
            'Colour'       => $label('colors',             $config['color_id'] ?? null),
            'Usage'        => $label('door_types',         $config['door_type_id'] ?? null),
            'Construction' => $label('construction_types', $config['construction_type_id'] ?? null),
            'Dimensions'   => ($w && $h) ? (($w / 10) . ' × ' . ($h / 10) . ' cm') : '—',
        ];
    }

    private function jsonBody(): array
    {
        $raw = file_get_contents('php://input');
        return json_decode($raw ?: '{}', true) ?? [];
    }

    private function csrfToken(): string
    {
        Session::start();
        if (empty($_SESSION['_cfg_csrf'])) {
            $_SESSION['_cfg_csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_cfg_csrf'];
    }

    private function verifyCsrf(): void
    {
        Session::start();
        $expected = $_SESSION['_cfg_csrf'] ?? '';
        $provided = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';

        if ($expected === '' || !hash_equals($expected, $provided)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Invalid request token.']);
            exit;
        }
    }
}
