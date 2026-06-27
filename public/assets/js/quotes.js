(function () {
  'use strict';

  var csrf = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';

  var statusMeta = {
    new:            { label: 'New',            color: 'blue'   },
    contacted:      { label: 'Contacted',      color: 'gold'   },
    quotation_sent: { label: 'Quotation Sent', color: 'purple' },
    in_progress:    { label: 'In Progress',    color: 'orange' },
    confirmed:      { label: 'Confirmed',      color: 'green'  },
    completed:      { label: 'Completed',      color: 'ink'    },
    cancelled:      { label: 'Cancelled',      color: 'red'    },
  };

  /* ─── Status update (show page) ─── */
  var updateBtn    = document.getElementById('updateStatusBtn');
  var newStatusSel = document.getElementById('newStatus');
  var statusNotes  = document.getElementById('statusNotes');
  var statusError  = document.getElementById('statusError');
  var statusErrMsg = document.getElementById('statusErrorMsg');
  var badgeEl      = document.getElementById('currentStatusBadge');

  if (updateBtn) {
    updateBtn.addEventListener('click', function () {
      var status = newStatusSel ? newStatusSel.value : '';
      if (!status) {
        showStatusError('Please select a status.');
        return;
      }

      hideStatusError();
      updateBtn.disabled = true;
      updateBtn.innerHTML = '<span class="btn-spinner-sm"></span> Updating…';

      var quoteId = updateBtn.dataset.id;
      var tok     = updateBtn.dataset.csrf || csrf;

      xhr('POST', '/door-showroom/admin/quotes/' + quoteId + '/status', {
        _csrf:        tok,
        status:       status,
        status_notes: statusNotes ? statusNotes.value.trim() : '',
      }, function (res) {
        updateBtn.disabled = false;
        updateBtn.textContent = 'Update Status';

        if (!res.success) {
          showStatusError(res.message || 'Update failed.');
          return;
        }

        if (badgeEl) {
          var m = statusMeta[res.status] || { label: res.status_label, color: 'ink' };
          badgeEl.className = 'qr-badge qr-badge--' + m.color;
          badgeEl.textContent = res.status_label;
        }

        window.location.reload();
      }, function () {
        updateBtn.disabled = false;
        updateBtn.textContent = 'Update Status';
        showStatusError('Network error — please try again.');
      });
    });
  }

  function showStatusError(msg) {
    if (statusError)  statusError.hidden  = false;
    if (statusErrMsg) statusErrMsg.textContent = msg;
  }

  function hideStatusError() {
    if (statusError) statusError.hidden = true;
  }

  /* ─── Delete (list + show page) ─── */
  var deleteModal     = document.getElementById('deleteModal');
  var deleteQuoteRef  = document.getElementById('deleteQuoteRef');
  var deleteCancelBtn = document.getElementById('deleteCancelBtn');
  var deleteConfirmBtn= document.getElementById('deleteConfirmBtn');
  var pendingDelete   = null;

  document.addEventListener('click', function (e) {
    var btn = e.target.closest('.js-delete-quote');
    if (btn) {
      e.preventDefault();
      pendingDelete = { id: btn.dataset.id, csrf: btn.dataset.csrf || csrf };
      if (deleteQuoteRef) deleteQuoteRef.textContent = btn.dataset.ref;
      if (deleteModal)    deleteModal.hidden = false;
    }
  });

  if (deleteCancelBtn) {
    deleteCancelBtn.addEventListener('click', function () {
      deleteModal.hidden = true;
      pendingDelete = null;
    });
  }

  if (deleteConfirmBtn) {
    deleteConfirmBtn.addEventListener('click', function () {
      if (!pendingDelete) return;
      deleteConfirmBtn.disabled = true;
      deleteConfirmBtn.innerHTML = '<span class="btn-spinner-sm"></span> Deleting…';

      xhr('POST', '/door-showroom/admin/quotes/' + pendingDelete.id + '/delete',
        { _csrf: pendingDelete.csrf },
        function (res) {
          if (res.success) {
            window.location.href = '/door-showroom/admin/quotes';
          } else {
            deleteConfirmBtn.disabled = false;
            deleteConfirmBtn.textContent = 'Delete';
            deleteModal.hidden = true;
          }
        },
        function () {
          deleteConfirmBtn.disabled = false;
          deleteConfirmBtn.textContent = 'Delete';
        }
      );
    });
  }

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && deleteModal && !deleteModal.hidden) {
      deleteModal.hidden = true;
      pendingDelete = null;
    }
  });

  /* ─── Utility ─── */
  function xhr(method, url, data, onSuccess, onError) {
    var req = new XMLHttpRequest();
    req.open(method, url, true);
    req.setRequestHeader('X-CSRF-Token', csrf);
    req.setRequestHeader('Content-Type', 'application/json');
    req.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    req.onload = function () {
      try { onSuccess(JSON.parse(req.responseText)); }
      catch (err) { if (onError) onError(err); }
    };
    req.onerror = function () { if (onError) onError(); };
    req.send(JSON.stringify(data));
  }
}());
