/**
 * m4p_blog
 *
 * @author    Modules4Presta <contact@modules4presta.io>
 * @copyright 2026 Nice Code sp. z o.o. (Modules4Presta)
 * @license   https://opensource.org/licenses/MIT MIT License
 */
(function () {
  'use strict';

  var uid = 0;

  function parse(json) {
    try {
      var value = JSON.parse(json);
      return Array.isArray(value) ? value : [];
    } catch (error) {
      return [];
    }
  }

  function editorTemplate(editor, type) {
    return editor.querySelector('.m4p-blog-editor__template[data-type="' + type + '"]');
  }

  function fieldValue(node, block) {
    var scope = node.dataset.fieldScope === 'settings' ? 'settings' : 'data';
    var source = block[scope] || {};
    var value = source[node.dataset.field];

    if (node.dataset.fieldType === 'lines') {
      return Array.isArray(value) ? value.join('\n') : value || '';
    }

    return value === undefined || value === null ? '' : String(value);
  }

  function initRichText(node) {
    node.id = 'm4p-blog-rte-' + ++uid;
    if (typeof tinySetup === 'function') {
      tinySetup({ selector: '#' + node.id });
    }
  }

  function buildBlock(editor, block) {
    var template = editorTemplate(editor, block.type);
    if (!template) {
      return null;
    }

    var node = template.content.firstElementChild.cloneNode(true);
    node.classList.toggle('m4p-blog-block--hidden', block.active === false);

    node.querySelectorAll('[data-field]').forEach(function (field) {
      field.value = fieldValue(field, block);
      if (field.dataset.fieldType === 'richtext') {
        initRichText(field);
      }
    });

    return node;
  }

  function readBlock(node) {
    var block = { type: node.dataset.type, active: !node.classList.contains('m4p-blog-block--hidden'), settings: {}, data: {} };

    node.querySelectorAll('[data-field]').forEach(function (field) {
      var scope = field.dataset.fieldScope === 'settings' ? 'settings' : 'data';
      var value = field.value;

      if (field.dataset.fieldType === 'richtext' && window.tinyMCE) {
        var instance = window.tinyMCE.get(field.id);
        if (instance) {
          value = instance.getContent();
        }
      }
      block[scope][field.dataset.field] = field.dataset.fieldType === 'lines'
        ? value.split('\n').map(function (line) { return line.trim(); }).filter(Boolean)
        : value;
    });

    return block;
  }

  function refresh(pane) {
    var blocks = pane.querySelectorAll('.m4p-blog-block');
    var empty = pane.querySelector('.m4p-blog-editor__empty');
    if (empty) {
      empty.classList.toggle('hidden', blocks.length > 0);
    }
  }

  function serialize(pane) {
    var blocks = [];
    pane.querySelectorAll('.m4p-blog-block').forEach(function (node) {
      blocks.push(readBlock(node));
    });
    pane.querySelector('.m4p-blog-editor__json').value = blocks.length ? JSON.stringify(blocks) : '';
  }

  function onBarClick(event, pane) {
    var button = event.target.closest('[data-action]');
    if (!button || !pane.contains(button)) {
      return;
    }

    var node = button.closest('.m4p-blog-block');
    var container = node.parentElement;

    if (button.dataset.action === 'remove') {
      if (window.confirm(pane.closest('.m4p-blog-editor').dataset.removeConfirm || '')) {
        node.remove();
        refresh(pane);
      }
      return;
    }
    if (button.dataset.action === 'toggle') {
      node.classList.toggle('m4p-blog-block--hidden');
      return;
    }
    if (button.dataset.action === 'up' && node.previousElementSibling) {
      container.insertBefore(node, node.previousElementSibling);
      return;
    }
    if (button.dataset.action === 'down' && node.nextElementSibling) {
      container.insertBefore(node.nextElementSibling, node);
    }
  }

  function setUpPane(editor, pane) {
    var container = pane.querySelector('.m4p-blog-editor__blocks');
    var json = pane.querySelector('.m4p-blog-editor__json');

    parse(json.value).forEach(function (block) {
      var node = buildBlock(editor, block);
      if (node) {
        container.appendChild(node);
      }
    });
    refresh(pane);

    pane.querySelectorAll('.m4p-blog-editor__add').forEach(function (button) {
      button.addEventListener('click', function () {
        var node = buildBlock(editor, { type: button.dataset.type, active: true, settings: {}, data: {} });
        if (node) {
          container.appendChild(node);
          refresh(pane);
        }
      });
    });

    pane.addEventListener('click', function (event) {
      onBarClick(event, pane);
    });
  }

  function setUp(editor) {
    var labels = {};
    try {
      labels = JSON.parse(editor.dataset.texts) || {};
    } catch (error) {
      labels = {};
    }
    editor.dataset.removeConfirm = labels.removeConfirm || '';

    editor.querySelectorAll('.m4p-blog-editor__lang').forEach(function (pane) {
      setUpPane(editor, pane);
    });

    var form = editor.closest('form');
    if (form) {
      form.addEventListener('submit', function () {
        if (window.tinyMCE && window.tinyMCE.triggerSave) {
          window.tinyMCE.triggerSave();
        }
        editor.querySelectorAll('.m4p-blog-editor__lang').forEach(serialize);
      });
    }
  }

  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.m4p-blog-editor').forEach(setUp);

    if (window.jQuery && window.jQuery.fn.datetimepicker) {
      window.jQuery('.m4p-blog-datetime').datetimepicker({ dateFormat: 'yy-mm-dd', timeFormat: 'HH:mm:ss' });
    }
  });
})();
