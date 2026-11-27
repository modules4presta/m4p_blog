{*
 * m4p_blog
 *
 * @author    Modules4Presta <contact@modules4presta.io>
 * @copyright 2026 Nice Code sp. z o.o. (Modules4Presta)
 * @license   https://opensource.org/licenses/MIT MIT License
 *}
<div class="m4p-blog-editor"
     data-texts="{$m4pBlogEditorTexts|@json_encode|escape:'html':'UTF-8'}">
  <p class="help-block">{$m4pBlogEditorTexts.noH1|escape:'html':'UTF-8'}</p>

  {foreach from=$m4pBlogEditor.languages item=language}
    <div class="m4p-blog-editor__lang" data-id-lang="{$language.id|intval}">
      {if $m4pBlogEditor.multilang}
        <h4 class="m4p-blog-editor__lang-name">{$language.name|escape:'html':'UTF-8'}</h4>
      {/if}
      <input type="hidden" name="content_blocks_{$language.id|intval}"
             class="m4p-blog-editor__json" value="{$language.json|escape:'html':'UTF-8'}">
      <div class="m4p-blog-editor__blocks"></div>
      <p class="m4p-blog-editor__empty">{$m4pBlogEditorTexts.empty|escape:'html':'UTF-8'}</p>
      <div class="m4p-blog-editor__actions">
        {foreach from=$m4pBlogEditor.types item=type}
          <button type="button" class="btn btn-default m4p-blog-editor__add" data-type="{$type.type|escape:'html':'UTF-8'}">
            <i class="material-icons">{$type.icon|escape:'html':'UTF-8'}</i>
            {$m4pBlogEditorTexts.add|escape:'html':'UTF-8'}: {$type.label|escape:'html':'UTF-8'}
          </button>
        {/foreach}
      </div>
    </div>
  {/foreach}

  {foreach from=$m4pBlogEditor.types item=type}
    <template class="m4p-blog-editor__template" data-type="{$type.type|escape:'html':'UTF-8'}">
      <div class="m4p-blog-block" data-type="{$type.type|escape:'html':'UTF-8'}">
        <div class="m4p-blog-block__bar">
          <span class="m4p-blog-block__label">
            <i class="material-icons">{$type.icon|escape:'html':'UTF-8'}</i>
            {$type.label|escape:'html':'UTF-8'}
          </span>
          <span class="m4p-blog-block__state">{$m4pBlogEditorTexts.hidden|escape:'html':'UTF-8'}</span>
          <span class="m4p-blog-block__buttons">
            <button type="button" class="btn btn-default btn-xs" data-action="up"
                    title="{$m4pBlogEditorTexts.up|escape:'html':'UTF-8'}"><i class="icon-arrow-up"></i></button>
            <button type="button" class="btn btn-default btn-xs" data-action="down"
                    title="{$m4pBlogEditorTexts.down|escape:'html':'UTF-8'}"><i class="icon-arrow-down"></i></button>
            <button type="button" class="btn btn-default btn-xs" data-action="toggle"
                    title="{$m4pBlogEditorTexts.hide|escape:'html':'UTF-8'}"><i class="icon-eye-close"></i></button>
            <button type="button" class="btn btn-default btn-xs" data-action="remove"
                    title="{$m4pBlogEditorTexts.remove|escape:'html':'UTF-8'}"><i class="icon-trash"></i></button>
          </span>
        </div>
        <div class="m4p-blog-block__fields">
          {foreach from=$type.fields item=field}
            <div class="m4p-blog-block__field">
              <label>{$field.label|escape:'html':'UTF-8'}</label>
              {if $field.translatable|default:false}{assign var=scope value='data'}{else}{assign var=scope value='settings'}{/if}
              {if $field.type == 'richtext'}
                <textarea class="m4p-blog-rte" rows="10"
                          data-field="{$field.name|escape:'html':'UTF-8'}"
                          data-field-type="richtext"
                          data-field-scope="{$scope}"
                          placeholder="{$field.placeholder|default:''|escape:'html':'UTF-8'}"></textarea>
              {elseif $field.type == 'lines'}
                <textarea class="form-control" rows="5"
                          data-field="{$field.name|escape:'html':'UTF-8'}"
                          data-field-type="lines"
                          data-field-scope="{$scope}"
                          placeholder="{$field.placeholder|default:''|escape:'html':'UTF-8'}"></textarea>
              {elseif $field.type == 'textarea' || $field.type == 'pairs'}
                <textarea class="form-control" rows="{if $field.type == 'pairs'}8{else}4{/if}"
                          data-field="{$field.name|escape:'html':'UTF-8'}"
                          data-field-type="{$field.type|escape:'html':'UTF-8'}"
                          data-field-scope="{$scope}"
                          placeholder="{$field.placeholder|default:''|escape:'html':'UTF-8'}"></textarea>
              {elseif $field.type == 'file'}
                <input type="hidden" data-field="{$field.name|escape:'html':'UTF-8'}" data-field-type="file" data-field-scope="{$scope}">
                <p class="m4p-blog-block__file" data-file-current></p>
                <input type="file" accept="application/pdf" data-file-input>
              {elseif $field.type == 'choice'}
                <select class="form-control"
                        data-field="{$field.name|escape:'html':'UTF-8'}"
                        data-field-type="choice"
                        data-field-scope="{$scope}">
                  {foreach from=$field.options key=value item=label}
                    <option value="{$value|escape:'html':'UTF-8'}">{$label|escape:'html':'UTF-8'}</option>
                  {/foreach}
                </select>
              {else}
                <input type="text" class="form-control"
                       data-field="{$field.name|escape:'html':'UTF-8'}"
                       data-field-type="text"
                       data-field-scope="{$scope}"
                       placeholder="{$field.placeholder|default:''|escape:'html':'UTF-8'}">
              {/if}
            </div>
          {/foreach}
        </div>
      </div>
    </template>
  {/foreach}
</div>
