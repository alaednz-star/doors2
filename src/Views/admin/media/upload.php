<?php
$e = fn(string $v): string => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
$flash = \App\Core\Session::getFlash('media_error');
?>

<div class="page-header">
  <div>
    <h1 class="page-title">Upload Media</h1>
    <p class="page-sub">JPEG, PNG or WebP · max 8 MB per file</p>
  </div>
  <a href="/door-showroom/admin/media" class="btn btn-outline">
    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"/></svg>
    Back to Library
  </a>
</div>

<?php if ($flash): ?>
  <div class="flash flash--error">
    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
    <span><?= $e($flash) ?></span>
    <button class="flash-close" onclick="this.parentElement.remove()">
      <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
    </button>
  </div>
<?php endif; ?>

<div class="form-layout">
  <div class="form-main">

    <form method="POST" action="/door-showroom/admin/media/store-form" enctype="multipart/form-data" id="uploadForm">
      <input type="hidden" name="_csrf" value="<?= $e($csrfToken) ?>" />

      <div class="form-card">
        <div class="form-card-body">

          <div class="ml-dropzone" id="mlDropzone">
            <input type="file" name="files[]" id="mlFileInput" multiple accept="image/jpeg,image/png,image/webp" class="ml-file-input" />
            <div class="ml-dropzone-inner" id="mlDropzoneInner">
              <svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.5" class="ml-dropzone-icon">
                <path d="M24 32V16m0 0l-6 6m6-6l6 6"/><rect x="4" y="4" width="40" height="40" rx="6"/>
                <path d="M12 36a6 6 0 01-6-6v-2M36 36a6 6 0 006-6v-2"/>
              </svg>
              <p class="ml-dropzone-label">Drag &amp; drop images here</p>
              <p class="ml-dropzone-sub">or <label for="mlFileInput" class="ml-dropzone-link">browse files</label></p>
              <p class="ml-dropzone-hint">JPEG, PNG, WebP · up to 8 MB each</p>
            </div>
          </div>

          <div class="ml-preview-grid" id="mlPreviewGrid" hidden></div>

          <div id="mlProgressWrap" hidden>
            <div class="ml-progress-bar-track"><div class="ml-progress-bar-fill" id="mlProgressFill"></div></div>
            <p class="ml-progress-label" id="mlProgressLabel"></p>
          </div>

        </div>
      </div>

      <div class="form-card" style="margin-top:16px">
        <div class="form-card-header"><h3 class="form-card-title">Assignment (optional)</h3></div>
        <div class="form-card-body">
          <p class="form-hint" style="margin-bottom:16px">Assign these files to an entity right away, or leave blank to assign later from the library.</p>
          <div class="form-row-2">
            <div class="form-group">
              <label class="form-label" for="entityType">Entity Type</label>
              <select name="entity_type" id="entityType" class="form-select">
                <option value="">— none —</option>
                <option value="product">Product</option>
                <option value="collection">Collection</option>
                <option value="color">Color</option>
              </select>
            </div>
            <div class="form-group" id="entityIdGroup" style="display:none">
              <label class="form-label" for="entityId">Select Item</label>
              <select name="entity_id" id="entityId" class="form-select"></select>
            </div>
          </div>
        </div>
      </div>

      <div style="display:flex;gap:10px;margin-top:20px">
        <button type="submit" class="btn btn-primary" id="uploadSubmitBtn" disabled>
          <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM6.293 6.707a1 1 0 010-1.414l3-3a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L11 5.414V13a1 1 0 11-2 0V5.414L7.707 6.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
          Upload
        </button>
        <a href="/door-showroom/admin/media" class="btn btn-outline">Cancel</a>
      </div>

    </form>

  </div>

  <div class="form-side">
    <div class="form-card">
      <div class="form-card-header"><h3 class="form-card-title">Guidelines</h3></div>
      <div class="form-card-body">
        <ul class="pr-how-list">
          <li class="pr-how-item"><span class="pr-how-num">1</span><span>Maximum 8 MB per file</span></li>
          <li class="pr-how-item"><span class="pr-how-num">2</span><span>Formats: JPEG, PNG, WebP</span></li>
          <li class="pr-how-item"><span class="pr-how-num">3</span><span>Max dimensions 6000 × 6000 px</span></li>
          <li class="pr-how-item"><span class="pr-how-num">4</span><span>Multiple files can be selected at once</span></li>
          <li class="pr-how-item"><span class="pr-how-num">5</span><span>Alt text can be added after upload</span></li>
        </ul>
      </div>
    </div>
  </div>

</div>

<?php
$entityJson = json_encode([
    'product'    => array_map(fn($r) => ['id' => $r['id'], 'name' => $r['name']], $entities['products']    ?? []),
    'collection' => array_map(fn($r) => ['id' => $r['id'], 'name' => $r['name']], $entities['collections'] ?? []),
    'color'      => array_map(fn($r) => ['id' => $r['id'], 'name' => $r['name']], $entities['colors']      ?? []),
], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT);
?>
<script src="/door-showroom/assets/js/media.js"></script>
<script>MediaLibrary.initUpload(<?= $entityJson ?>);</script>
