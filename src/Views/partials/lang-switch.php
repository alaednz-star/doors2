<?php
/**
 * Language switcher partial — FR · EN · AR.
 *
 * @var string $variant  optional extra class suffix ('mobile' for the drawer)
 */
use App\Core\I18n;

$cur     = I18n::lang();
$variant = $variant ?? '';
$cls     = 'lang-switch' . ($variant ? ' lang-switch--' . $variant : '');
$codes   = array_keys(I18n::LANGS);
?>
<div class="<?= htmlspecialchars($cls, ENT_QUOTES) ?>" role="group" aria-label="<?= htmlspecialchars(I18n::t('lang.switch_aria'), ENT_QUOTES) ?>">
  <?php foreach ($codes as $i => $code):
      $meta = I18n::LANGS[$code];
      $active = $code === $cur;
  ?>
    <a class="lang-switch__item<?= $active ? ' is-active' : '' ?>"
       href="/door-showroom/lang?set=<?= $code ?>"
       hreflang="<?= $code ?>"
       lang="<?= $code ?>"
       <?= $active ? 'aria-current="true"' : '' ?>><?= htmlspecialchars($meta['label'], ENT_QUOTES) ?></a>
    <?php if ($i < count($codes) - 1): ?><span class="lang-switch__sep" aria-hidden="true"></span><?php endif; ?>
  <?php endforeach; ?>
</div>
