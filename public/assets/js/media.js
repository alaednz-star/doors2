var MediaLibrary = (function () {

  var csrf       = '';
  var entityData = {};
  var activeId   = null;

  function getcsrf() {
    var m = document.querySelector('meta[name="csrf-token"]');
    return m ? m.getAttribute('content') : '';
  }

  function xhr(method, url, payload, cb) {
    var x = new XMLHttpRequest();
    x.open(method, url, true);
    x.setRequestHeader('Content-Type', 'application/json');
    x.setRequestHeader('X-CSRF-Token', csrf);
    x.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    x.onreadystatechange = function () {
      if (x.readyState !== 4) return;
      try { cb(JSON.parse(x.responseText)); } catch (e) { cb({ success: false, message: 'Unexpected error.' }); }
    };
    x.send(payload ? JSON.stringify(payload) : JSON.stringify({ _csrf: csrf }));
  }

  function formatBytes(b) {
    b = parseInt(b, 10) || 0;
    if (b >= 1048576) return (b / 1048576).toFixed(1) + ' MB';
    if (b >= 1024)    return (b / 1024).toFixed(1) + ' KB';
    return b + ' B';
  }

  function showFlash(msg, type) {
    var content = document.querySelector('.admin-content');
    if (!content) return;
    var el = document.createElement('div');
    el.className = 'flash flash--' + type;
    el.innerHTML = '<span>' + msg + '</span><button class="flash-close" onclick="this.parentElement.remove()"><svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg></button>';
    content.insertBefore(el, content.firstChild);
    setTimeout(function () { if (el.parentElement) el.remove(); }, 5000);
  }

  function init(entities) {
    csrf       = getcsrf();
    entityData = entities || {};

    initSearch();
    initGrid();
    initOverlay();
    initDeleteModal();
  }

  function getcsrf() {
    var m = document.querySelector('meta[name="csrf-token"]');
    return m ? m.getAttribute('content') : '';
  }

  function initSearch() {
    var input  = document.getElementById('mlSearch');
    var hidden = document.getElementById('mlTypeHidden');
    var form   = document.getElementById('mlSearchForm');
    if (!input || !form) return;

    var timer;
    input.addEventListener('input', function () {
      clearTimeout(timer);
      timer = setTimeout(function () { form.submit(); }, 420);
    });
  }

  function initGrid() {
    document.querySelectorAll('.ml-thumb-btn').forEach(function (btn) {
      btn.addEventListener('click', function () {
        openPreview(parseInt(btn.dataset.id, 10));
      });
    });
  }

  function openPreview(id) {
    activeId = id;
    xhr('GET', '/door-showroom/admin/media/' + id + '/preview', null, function (data) {
      if (!data.success) return;
      populatePanel(data.media);
      document.getElementById('mlOverlay').hidden = false;
      document.body.style.overflow = 'hidden';
    });
  }

  function populatePanel(m) {
    var overlay = document.getElementById('mlOverlay');
    if (!overlay) return;

    var img = document.getElementById('mlPanelImg');
    img.src = m.url;
    img.alt = m.alt_text || '';

    setText('mlMetaName', m.original_name || m.filename);
    setText('mlMetaSize', m.size_formatted || formatBytes(m.file_size));
    setText('mlMetaDims', (m.width && m.height) ? m.width + ' × ' + m.height + ' px' : '—');
    setText('mlMetaMime', m.mime_type || '—');
    setText('mlMetaDate', m.created_at ? m.created_at.replace('T', ' ').slice(0, 16) : '—');

    var altInput = document.getElementById('mlAltInput');
    if (altInput) altInput.value = m.alt_text || '';

    var openLink = document.getElementById('mlOpenLink');
    if (openLink) openLink.href = m.url;

    var typeSelect = document.getElementById('mlAssignType');
    var idSelect   = document.getElementById('mlAssignId');
    var idWrap     = document.getElementById('mlAssignIdWrap');

    if (typeSelect) {
      typeSelect.value = m.entity_type || '';
      refreshEntityIdSelect(m.entity_type, m.entity_id);
    }
  }

  function setText(id, val) {
    var el = document.getElementById(id);
    if (el) el.textContent = val;
  }

  function refreshEntityIdSelect(type, selectedId) {
    var idSelect = document.getElementById('mlAssignId');
    var idWrap   = document.getElementById('mlAssignIdWrap');
    if (!idSelect || !idWrap) return;

    if (!type || !entityData[type] || entityData[type].length === 0) {
      idWrap.style.display = 'none';
      return;
    }

    idWrap.style.display = '';
    idSelect.innerHTML   = '';

    entityData[type].forEach(function (item) {
      var opt = document.createElement('option');
      opt.value       = item.id;
      opt.textContent = item.name;
      if (parseInt(selectedId, 10) === parseInt(item.id, 10)) opt.selected = true;
      idSelect.appendChild(opt);
    });
  }

  function initOverlay() {
    var overlay = document.getElementById('mlOverlay');
    if (!overlay) return;

    document.getElementById('mlPanelClose').addEventListener('click', closeOverlay);

    overlay.addEventListener('click', function (e) {
      if (e.target === overlay) closeOverlay();
    });

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && overlay && !overlay.hidden) closeOverlay();
    });

    var typeSelect = document.getElementById('mlAssignType');
    if (typeSelect) {
      typeSelect.addEventListener('change', function () {
        refreshEntityIdSelect(typeSelect.value, null);
      });
    }

    var altSave = document.getElementById('mlAltSave');
    if (altSave) {
      altSave.addEventListener('click', function () {
        if (!activeId) return;
        var val = document.getElementById('mlAltInput').value;
        xhr('POST', '/door-showroom/admin/media/' + activeId + '/alt', { alt_text: val, _csrf: csrf }, function (data) {
          var msg = document.getElementById('mlAltMsg');
          if (msg) {
            msg.hidden    = false;
            msg.textContent = data.success ? 'Saved.' : (data.message || 'Failed.');
            msg.className  = 'ml-alt-msg ml-alt-msg--' + (data.success ? 'ok' : 'err');
            setTimeout(function () { msg.hidden = true; }, 2500);
          }
        });
      });
    }

    var assignSave = document.getElementById('mlAssignSave');
    if (assignSave) {
      assignSave.addEventListener('click', function () {
        if (!activeId) return;
        var type     = document.getElementById('mlAssignType').value;
        var idSelect = document.getElementById('mlAssignId');
        var entityId = (type && idSelect && idSelect.value) ? parseInt(idSelect.value, 10) : null;

        xhr('POST', '/door-showroom/admin/media/' + activeId + '/assign',
          { entity_type: type || null, entity_id: entityId, _csrf: csrf },
          function (data) {
            var msg = document.getElementById('mlAssignMsg');
            if (msg) {
              msg.hidden     = false;
              msg.textContent = data.success ? 'Assignment saved.' : (data.message || 'Failed.');
              msg.className   = 'ml-alt-msg ml-alt-msg--' + (data.success ? 'ok' : 'err');
              setTimeout(function () { msg.hidden = true; }, 2500);
            }
            if (data.success) {
              var card = document.getElementById('ml-card-' + activeId);
              if (card) {
                var badge = card.querySelector('.ml-entity-badge');
                if (type) {
                  if (!badge) {
                    badge = document.createElement('span');
                    card.querySelector('.ml-thumb-btn').appendChild(badge);
                  }
                  badge.className  = 'ml-entity-badge ml-entity-badge--' + type;
                  badge.textContent = type.charAt(0).toUpperCase() + type.slice(1);
                } else if (badge) {
                  badge.remove();
                }
              }
            }
          }
        );
      });
    }

    var deleteBtn = document.getElementById('mlDeleteBtn');
    if (deleteBtn) {
      deleteBtn.addEventListener('click', function () {
        document.getElementById('mlDeleteModal').hidden = false;
      });
    }
  }

  function closeOverlay() {
    var overlay = document.getElementById('mlOverlay');
    if (overlay) overlay.hidden = true;
    document.body.style.overflow = '';
    activeId = null;
  }

  function initDeleteModal() {
    var modal   = document.getElementById('mlDeleteModal');
    var confirm = document.getElementById('mlDeleteConfirm');
    var cancel  = document.getElementById('mlDeleteCancel');
    var label   = document.getElementById('mlDeleteLabel');
    var spinner = document.getElementById('mlDeleteSpinner');
    if (!modal || !confirm) return;

    cancel.addEventListener('click', function () { modal.hidden = true; });
    modal.addEventListener('click', function (e) { if (e.target === modal) modal.hidden = true; });

    confirm.addEventListener('click', function () {
      if (!activeId) return;
      var id = activeId;
      label.textContent = 'Deleting…';
      spinner.hidden    = false;
      confirm.disabled  = true;

      xhr('POST', '/door-showroom/admin/media/' + id + '/delete', null, function (data) {
        label.textContent = 'Delete';
        spinner.hidden    = true;
        confirm.disabled  = false;
        modal.hidden      = true;

        if (data.success) {
          var card = document.getElementById('ml-card-' + id);
          if (card) {
            card.style.opacity    = '0';
            card.style.transition = 'opacity 0.2s';
            setTimeout(function () { card.remove(); }, 220);
          }
          closeOverlay();
          showFlash('Image deleted.', 'success');
        } else {
          showFlash(data.message || 'Delete failed.', 'error');
        }
      });
    });
  }

  function initUpload(entities) {
    csrf       = getcsrf();
    entityData = entities || {};

    var dropzone   = document.getElementById('mlDropzone');
    var fileInput  = document.getElementById('mlFileInput');
    var previewGrid = document.getElementById('mlPreviewGrid');
    var submitBtn  = document.getElementById('uploadSubmitBtn');
    var entityType = document.getElementById('entityType');
    var entityId   = document.getElementById('entityId');
    var entityGrp  = document.getElementById('entityIdGroup');
    if (!dropzone || !fileInput) return;

    fileInput.addEventListener('change', function () {
      renderPreviews(fileInput.files);
      if (fileInput.files.length > 0 && submitBtn) submitBtn.disabled = false;
    });

    dropzone.addEventListener('dragover', function (e) {
      e.preventDefault();
      dropzone.classList.add('is-dragover');
    });
    dropzone.addEventListener('dragleave', function () {
      dropzone.classList.remove('is-dragover');
    });
    dropzone.addEventListener('drop', function (e) {
      e.preventDefault();
      dropzone.classList.remove('is-dragover');
      var dt = e.dataTransfer;
      if (dt && dt.files.length) {
        fileInput.files = dt.files;
        renderPreviews(dt.files);
        if (submitBtn) submitBtn.disabled = false;
      }
    });

    if (entityType) {
      entityType.addEventListener('change', function () {
        var type = entityType.value;
        if (!type || !entityData[type] || entityData[type].length === 0) {
          entityGrp.style.display = 'none';
          return;
        }
        entityGrp.style.display = '';
        entityId.innerHTML = '';
        entityData[type].forEach(function (item) {
          var opt = document.createElement('option');
          opt.value       = item.id;
          opt.textContent = item.name;
          entityId.appendChild(opt);
        });
      });
    }

    function renderPreviews(files) {
      if (!previewGrid) return;
      previewGrid.innerHTML = '';
      previewGrid.hidden    = false;
      Array.from(files).forEach(function (f) {
        var reader = new FileReader();
        reader.onload = function (ev) {
          var div = document.createElement('div');
          div.className = 'ml-preview-item';
          var img = document.createElement('img');
          img.src       = ev.target.result;
          img.className = 'ml-preview-thumb';
          var name = document.createElement('span');
          name.className   = 'ml-preview-name';
          name.textContent = f.name.length > 20 ? f.name.slice(0, 17) + '…' : f.name;
          div.appendChild(img);
          div.appendChild(name);
          previewGrid.appendChild(div);
        };
        reader.readAsDataURL(f);
      });
    }
  }

  return { init: init, initUpload: initUpload };

}());
