{*
 * m4p_blog
 *
 * @author    Modules4Presta <contact@modules4presta.io>
 * @copyright 2026 Nice Code sp. z o.o. (Modules4Presta)
 * @license   https://opensource.org/licenses/MIT MIT License
 *}
<dl class="m4p-blog-block m4p-blog-facts">
  {foreach from=$m4pBlogData.rows item=row}
    <div class="m4p-blog-facts__item">
      {if $row.label}<dt>{$row.label|escape:'html':'UTF-8'}</dt>{/if}
      <dd>{$row.value|escape:'html':'UTF-8'}</dd>
    </div>
  {/foreach}
</dl>
