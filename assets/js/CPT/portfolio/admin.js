/**
 * UW Portfolio Admin Form
 *  - 대표 이미지 미디어 선택
 *  - 자체 콤보박스(서비스명 단일 / 태그 다중)
 */
(function ($) {
  'use strict';

  $(function () {
    initThumbnail();
    initComboboxes();
  });

  /* ==========================================================================
     대표 이미지
     ========================================================================== */
  function initThumbnail() {
    var $hidden  = $('#uw_port_thumb_id');
    var $preview = $('#uw_port_thumb_preview');
    var $btn     = $('#uw_port_thumb_btn');
    var $clear   = $('#uw_port_thumb_clear');
    if (!$hidden.length) return;

    function renderImg(url) {
      $preview.empty().append('<img src="' + url + '" alt="">');
    }
    function renderEmpty() {
      $preview.empty().append('<span class="uw-port-thumb-placeholder">이미지 없음</span>');
    }

    var $altWarn = $('#uw_port_alt_warn');
    function toggleAltWarn(att) {
      if (!$altWarn.length) return;
      var alt = att && (att.alt || '');
      if (att && (!alt || alt === '')) {
        var $a = $altWarn.find('a');
        if ($a.length && att.id) {
          var base = $a.attr('href').split('?')[0];
          $a.attr('href', base + '?post=' + att.id + '&action=edit');
        }
        $altWarn.prop('hidden', false);
      } else {
        $altWarn.prop('hidden', true);
      }
    }

    $btn.on('click', function (e) {
      e.preventDefault();
      var frame = wp.media({
        title: '대표 이미지 선택',
        multiple: false,
        library: { type: 'image' },
        button: { text: '선택' }
      });
      frame.on('select', function () {
        var att = frame.state().get('selection').first().toJSON();
        $hidden.val(att.id);
        var url = (att.sizes && att.sizes.medium ? att.sizes.medium.url : att.url);
        renderImg(url);
        $btn.text('이미지 변경');
        $clear.show();
        toggleAltWarn(att);
      });
      frame.open();
    });

    $clear.on('click', function (e) {
      e.preventDefault();
      $hidden.val('');
      renderEmpty();
      $btn.text('이미지 선택');
      $clear.hide();
      if ($altWarn.length) $altWarn.prop('hidden', true);
    });
  }

  /* ==========================================================================
     자체 콤보박스 (서비스명 single / 태그 multi)
     ========================================================================== */
  function initComboboxes() {
    document.querySelectorAll('[data-uw-combo]').forEach(initOne);
  }

  function initOne(root) {
    var mode    = root.getAttribute('data-mode') || 'single';
    var options = [];
    try { options = JSON.parse(root.getAttribute('data-options') || '[]'); } catch (e) {}

    if (mode === 'multi') {
      initMulti(root, options);
    } else {
      initSingle(root, options);
    }
  }

  // ── 단일 (서비스명) ───────────────────────────────────────────────────────
  function initSingle(root, options) {
    var input = root.querySelector('.uw-combo-input');
    if (!input) return;

    var dropdown = createDropdown(root);

    function open() {
      renderOptions(dropdown, filterOptions(options, input.value, []), function (val) {
        input.value = val;
        close();
        input.dispatchEvent(new Event('change'));
      }, -1);
      root.classList.add('is-open');
    }
    function close() { root.classList.remove('is-open'); }

    input.addEventListener('focus', open);
    input.addEventListener('input', open);
    input.addEventListener('keydown', function (e) {
      handleKeydown(e, dropdown, function (val) {
        input.value = val;
        close();
      }, close);
    });
    document.addEventListener('click', function (e) {
      if (!root.contains(e.target)) close();
    });
  }

  // ── 다중 (태그) ───────────────────────────────────────────────────────────
  function initMulti(root, options) {
    var hidden = root.querySelector('input[type="hidden"]');
    var input  = root.querySelector('.uw-combo-input');
    var chips  = root.querySelector('[data-uw-combo-chips]');
    if (!hidden || !input || !chips) return;

    var values = parseTagsRaw(hidden.value);
    var dropdown = createDropdown(root);

    function syncHidden() { hidden.value = values.join(', '); }

    function renderChips() {
      chips.innerHTML = '';
      values.forEach(function (v, idx) {
        var chip = document.createElement('span');
        chip.className = 'uw-combo-chip';
        var label = document.createElement('span');
        label.className = 'uw-combo-chip-label';
        label.textContent = '#' + v;
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'uw-combo-chip-remove';
        btn.setAttribute('aria-label', v + ' 삭제');
        btn.textContent = '×';
        btn.addEventListener('click', function (e) {
          e.preventDefault();
          values.splice(idx, 1);
          syncHidden();
          renderChips();
          input.focus();
          openDrop();
        });
        chip.appendChild(label);
        chip.appendChild(btn);
        chips.appendChild(chip);
      });
    }

    function addValue(v) {
      v = String(v || '').trim().replace(/^#+/, '');
      if (v === '') return;
      if (values.indexOf(v) !== -1) return;
      values.push(v);
      syncHidden();
      renderChips();
    }

    function openDrop() {
      var filtered = filterOptions(options, input.value, values);
      renderOptions(dropdown, filtered, function (val) {
        addValue(val);
        input.value = '';
        openDrop();
        input.focus();
      }, -1);
      root.classList.add('is-open');
    }

    function close() { root.classList.remove('is-open'); }

    input.addEventListener('focus', openDrop);
    input.addEventListener('input', openDrop);
    input.addEventListener('keydown', function (e) {
      if (e.key === 'Enter') {
        e.preventDefault();
        var hi = dropdown.querySelector('.uw-combo-opt.is-hi');
        if (hi) {
          addValue(hi.textContent);
        } else if (input.value.trim() !== '') {
          addValue(input.value);
        }
        input.value = '';
        openDrop();
        return;
      }
      if (e.key === ',' || e.key === 'Tab') {
        if (input.value.trim() !== '') {
          e.preventDefault();
          addValue(input.value);
          input.value = '';
          openDrop();
        }
        return;
      }
      if (e.key === 'Backspace' && input.value === '' && values.length > 0) {
        e.preventDefault();
        values.pop();
        syncHidden();
        renderChips();
        openDrop();
        return;
      }
      handleKeydown(e, dropdown, function (val) {
        addValue(val);
        input.value = '';
        openDrop();
      }, close);
    });

    document.addEventListener('click', function (e) {
      if (!root.contains(e.target)) close();
    });

    // 초기 렌더
    renderChips();
    syncHidden();
  }

  /* ── 공통 ────────────────────────────────────────────────────────────────── */
  function createDropdown(root) {
    var d = document.createElement('div');
    d.className = 'uw-combo-dropdown';
    root.appendChild(d);
    return d;
  }

  function filterOptions(options, query, excluded) {
    var q = (query || '').trim().toLowerCase();
    return options.filter(function (opt) {
      if (excluded.indexOf(opt) !== -1) return false;
      if (q === '') return true;
      return opt.toLowerCase().indexOf(q) !== -1;
    });
  }

  function renderOptions(dropdown, list, onSelect, hiIdx) {
    dropdown.innerHTML = '';
    if (list.length === 0) {
      var empty = document.createElement('div');
      empty.className = 'uw-combo-empty';
      empty.textContent = '직접 입력 후 Enter';
      dropdown.appendChild(empty);
      return;
    }
    list.forEach(function (opt, idx) {
      var el = document.createElement('button');
      el.type = 'button';
      el.className = 'uw-combo-opt' + (idx === hiIdx ? ' is-hi' : '');
      el.textContent = opt;
      el.addEventListener('mousedown', function (e) { e.preventDefault(); });
      el.addEventListener('click', function () { onSelect(opt); });
      dropdown.appendChild(el);
    });
  }

  function handleKeydown(e, dropdown, onPick, onClose) {
    var opts = Array.prototype.slice.call(dropdown.querySelectorAll('.uw-combo-opt'));
    var curr = dropdown.querySelector('.uw-combo-opt.is-hi');
    var idx  = curr ? opts.indexOf(curr) : -1;

    if (e.key === 'ArrowDown') {
      e.preventDefault();
      idx = (idx + 1) % Math.max(opts.length, 1);
      opts.forEach(function (o, i) { o.classList.toggle('is-hi', i === idx); });
    } else if (e.key === 'ArrowUp') {
      e.preventDefault();
      idx = idx <= 0 ? opts.length - 1 : idx - 1;
      opts.forEach(function (o, i) { o.classList.toggle('is-hi', i === idx); });
    } else if (e.key === 'Escape') {
      onClose();
    } else if (e.key === 'Enter') {
      if (curr) {
        e.preventDefault();
        onPick(curr.textContent);
      }
    }
  }

  function parseTagsRaw(str) {
    if (!str) return [];
    return String(str).split(',').map(function (s) {
      return s.trim().replace(/^#+/, '');
    }).filter(Boolean);
  }
})(jQuery);
