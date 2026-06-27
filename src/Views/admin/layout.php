<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="robots" content="noindex, nofollow" />
  <meta name="csrf-token" content="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>" />
  <title><?= htmlspecialchars($pageTitle ?? 'Dashboard', ENT_QUOTES, 'UTF-8') ?> — Doors Admin</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="/door-showroom/assets/css/admin.css" />
</head>
<body>

<div class="admin-shell">

  <?php
    $currentPage = $currentPage ?? 'dashboard';
    // Ensure the "new leads" badge/counter is available on EVERY admin page,
    // not only the dashboard. Only fills the count if a controller didn't set it.
    if (!isset($stats['new_quotes'])) {
        try {
            $stats['new_quotes'] = (int) \App\Core\Database::conn()
                ->query("SELECT COUNT(*) FROM quote_requests WHERE status = 'new'")
                ->fetchColumn();
        } catch (\Throwable $e) {
            $stats['new_quotes'] = 0;
        }
    }
    require __DIR__ . '/partials/sidebar.php';
  ?>

  <div class="admin-main" id="adminMain">

    <?php require __DIR__ . '/partials/header.php'; ?>

    <main class="admin-content">
      <?php require __DIR__ . '/' . ($view ?? 'dashboard') . '.php'; ?>
    </main>

  </div>

</div>

<script src="/door-showroom/assets/js/admin.js"></script>
</body>
</html>
