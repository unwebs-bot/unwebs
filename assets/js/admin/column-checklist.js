/**
 * UW Column Checklist (Gutenberg)
 *
 * 전문 칼럼 편집 시 Phase 2 SEO 루브릭을 실시간으로 검증.
 * wp.data 를 subscribe 해서 post title/content/excerpt/meta 등이 변할 때마다 체크 갱신.
 */

(function () {
  'use strict';

  if (typeof wp === 'undefined' || !wp.data || !wp.domReady) return;

  wp.domReady(function () {
    var box = document.getElementById('uw-column-checklist');
    if (!box) return;

    var markEls = {};
    box.querySelectorAll('li[data-key]').forEach(function (li) {
      markEls[li.getAttribute('data-key')] = li;
    });

    var passEl  = box.querySelector('.uw-ccl-pass');
    var totalEl = box.querySelector('.uw-ccl-total');
    var barEl   = box.querySelector('.uw-ccl-progress-bar');
    totalEl.textContent = Object.keys(markEls).length;

    var last = '';

    wp.data.subscribe(function () {
      var snap = collectState();
      var key  = JSON.stringify(snap);
      if (key === last) return; // noop
      last = key;

      var results = evaluate(snap);
      var pass = 0;
      Object.keys(results).forEach(function (k) {
        var li = markEls[k];
        if (!li) return;
        var ok = results[k];
        li.classList.toggle('is-pass', ok);
        li.classList.toggle('is-fail', !ok);
        if (ok) pass++;
      });
      passEl.textContent = pass;
      var total = Object.keys(results).length;
      barEl.style.width = Math.round((pass / total) * 100) + '%';
      barEl.classList.toggle('is-full', pass === total);
    });
  });

  function collectState() {
    var editor = wp.data.select('core/editor');
    if (!editor) return null;
    var postType = editor.getCurrentPostType();
    if (postType !== 'column') return null;

    return {
      title:       editor.getEditedPostAttribute('title') || '',
      slug:        editor.getEditedPostSlug() || editor.getEditedPostAttribute('slug') || '',
      excerpt:     editor.getEditedPostAttribute('excerpt') || '',
      content:     editor.getEditedPostContent() || '',
      thumbnail:   editor.getEditedPostAttribute('featured_media') || 0,
      categories:  editor.getEditedPostAttribute('column_category') || [],
      tags:        editor.getEditedPostAttribute('column_tag') || [],
    };
  }

  function evaluate(s) {
    if (!s) return {};

    var title   = s.title.trim();
    var slug    = (s.slug || '').trim();
    var excerpt = s.excerpt.trim();
    var html    = s.content || '';

    // 본문을 DOM 파싱 (보조)
    var doc = null;
    try {
      doc = new DOMParser().parseFromString('<body>' + html + '</body>', 'text/html');
    } catch (e) { /* noop */ }

    var textOnly = doc ? (doc.body.textContent || '') : html.replace(/<[^>]+>/g, '');
    var charCount = textOnly.replace(/\s+/g, '').length;

    // H2 섹션마다 수치 체크
    var h2s = doc ? doc.body.querySelectorAll('h2') : [];
    var numberedSections = 0;
    h2s.forEach(function (h2) {
      // h2 다음 형제 중 다음 h2 나오기 전까지의 텍스트
      var buf = '';
      var node = h2.nextElementSibling;
      while (node && node.tagName !== 'H2') {
        buf += ' ' + (node.textContent || '');
        node = node.nextElementSibling;
      }
      if (/\d/.test(buf)) numberedSections++;
    });

    // 링크 분류
    var internalLinks = 0, externalLinks = 0;
    if (doc) {
      var anchors = doc.body.querySelectorAll('a[href]');
      anchors.forEach(function (a) {
        var href = a.getAttribute('href') || '';
        if (!href) return;
        if (/^https?:\/\//i.test(href)) {
          if (href.indexOf(location.hostname) !== -1) internalLinks++;
          else externalLinks++;
        } else if (href.charAt(0) === '/' || href.charAt(0) === '#') {
          internalLinks++;
        }
      });
    }

    var quoteCount = doc ? doc.body.querySelectorAll('blockquote').length : 0;

    // 이미지 alt 체크
    var imgs = doc ? doc.body.querySelectorAll('img') : [];
    var imgMissingAlt = 0;
    imgs.forEach(function (img) {
      var alt = img.getAttribute('alt');
      if (alt === null || alt === '') imgMissingAlt++;
    });

    // 영문 슬러그 판정
    var slugOk = /^[a-z0-9]+(?:-[a-z0-9]+){2,}$/i.test(slug);

    return {
      title:          title.length > 0 && title.length <= 60,
      slug:           slugOk,
      excerpt:        excerpt.length >= 120 && excerpt.length <= 160,
      thumbnail:      !!s.thumbnail && s.thumbnail > 0,
      category:       Array.isArray(s.categories) && s.categories.length >= 1,
      tags:           Array.isArray(s.tags) && s.tags.length >= 3 && s.tags.length <= 5,
      wordcount:      charCount >= 2500,
      h2:             h2s.length >= 3,
      numbers:        h2s.length > 0 && numberedSections === h2s.length,
      external_links: externalLinks >= 3,
      internal_links: internalLinks >= 3,
      quote:          quoteCount >= 1,
      images_alt:     imgs.length > 0 && imgMissingAlt === 0,
    };
  }
})();
