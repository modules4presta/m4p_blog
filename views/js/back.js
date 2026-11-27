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
    if (node.dataset.fieldType === 'pairs') {
      return Array.isArray(value)
        ? value.map(function (pair) { return (pair.q || '') + '\n' + (pair.a || ''); }).join('\n\n')
        : value || '';
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
      if (field.dataset.fieldType === 'file' && field.value) {
        var current = field.parentElement.querySelector('[data-file-current]');
        if (current) {
          current.textContent = (editor.dataset.currentFile || '') + ' ' + field.value;
        }
      }
    });

    return node;
  }

  function readBlock(node) {
    var block = { type: node.dataset.type, active: !node.classList.contains('m4p-blog-block--hidden'), settings: {}, data: {} };

    node.querySelectorAll('[data-field]').forEach(function (field) {
      var scope = field.dataset.fieldScope === 'settings' ? 'settings' : 'data';
      var value = field.value;

      if (field.dataset.fieldType === 'file') {
        var input = field.parentElement.querySelector('[data-file-input]');
        if (input && input.files && input.files.length) {
          var token = 'f' + Date.now().toString(36) + (++uid);
          input.name = 'm4p_blog_file[' + token + ']';
          value = 'upload:' + token;
        }
      }
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
    editor.dataset.currentFile = labels.currentFile || '';

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

  function setUpPicker(root) {
    var data = root.dataset;
    var value = root.querySelector('[data-m4p-blog-picker-value]');
    var list = root.querySelector('[data-m4p-blog-picker-list]');
    var search = root.querySelector('[data-m4p-blog-picker-search]');
    var results = root.querySelector('[data-m4p-blog-picker-results]');
    var products = parse(data.products || '[]');
    var timer = null;
    var request = 0;

    function button(text, action, index) {
      var el = document.createElement('button');
      el.type = 'button';
      el.className = 'btn btn-default btn-xs';
      el.textContent = text;
      el.dataset.action = action;
      el.dataset.index = String(index);
      return el;
    }

    function render() {
      value.value = products.map(function (product) { return product.id; }).join(',');
      list.textContent = '';
      products.forEach(function (product, index) {
        var item = document.createElement('li');
        var name = document.createElement('span');
        name.className = 'm4p-blog-picker__name';
        name.textContent = product.name + (product.reference ? ' – ' + product.reference : '');
        item.appendChild(name);
        if (index > 0) { item.appendChild(button(data.labelUp, 'up', index)); }
        if (index < products.length - 1) { item.appendChild(button(data.labelDown, 'down', index)); }
        item.appendChild(button(data.labelRemove, 'remove', index));
        list.appendChild(item);
      });
    }

    function closeResults() {
      results.hidden = true;
      results.textContent = '';
    }

    function showResults(found) {
      var chosen = products.map(function (product) { return product.id; });
      results.textContent = '';
      found = found.filter(function (product) { return chosen.indexOf(product.id) === -1; });
      if (!found.length) {
        var none = document.createElement('li');
        none.className = 'm4p-blog-picker__none';
        none.textContent = data.labelNone;
        results.appendChild(none);
      }
      found.forEach(function (product) {
        var item = document.createElement('li');
        var choose = document.createElement('button');
        choose.type = 'button';
        choose.textContent = product.name + (product.reference ? ' – ' + product.reference : '');
        choose.addEventListener('click', function () {
          products.push(product);
          render();
          closeResults();
          search.value = '';
          search.focus();
        });
        item.appendChild(choose);
        results.appendChild(item);
      });
      results.hidden = false;
    }

    function find() {
      var query = search.value.trim();
      if (query.length < 2) { closeResults(); return; }
      var current = ++request;
      fetch(data.searchUrl + '&q=' + encodeURIComponent(query), { credentials: 'same-origin', headers: { Accept: 'application/json' } })
        .then(function (response) { return response.ok ? response.json() : []; })
        .then(function (found) { if (current === request) { showResults(Array.isArray(found) ? found : []); } })
        .catch(function () { if (current === request) { showResults([]); } });
    }

    list.addEventListener('click', function (event) {
      var target = event.target.closest('[data-action]');
      if (!target) { return; }
      var index = parseInt(target.dataset.index, 10);
      if (target.dataset.action === 'remove') {
        products.splice(index, 1);
      } else {
        var to = target.dataset.action === 'up' ? index - 1 : index + 1;
        products.splice(to, 0, products.splice(index, 1)[0]);
      }
      render();
    });
    search.addEventListener('input', function () {
      window.clearTimeout(timer);
      timer = window.setTimeout(find, 250);
    });
    search.addEventListener('keydown', function (event) {
      if (event.key === 'Enter') { event.preventDefault(); }
      if (event.key === 'Escape') { closeResults(); }
    });
    document.addEventListener('click', function (event) {
      if (!root.contains(event.target)) { closeResults(); }
    });
    render();
  }

  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.m4p-blog-editor').forEach(setUp);
    document.querySelectorAll('[data-m4p-blog-picker]').forEach(setUpPicker);

    if (window.jQuery && window.jQuery.fn.datetimepicker) {
      window.jQuery('.m4p-blog-datetime').datetimepicker({ dateFormat: 'yy-mm-dd', timeFormat: 'HH:mm:ss' });
    }
  });
})();
