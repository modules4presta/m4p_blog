{*
 * m4p_blog
 *
 * @author    Modules4Presta <contact@modules4presta.io>
 * @copyright 2026 Nice Code sp. z o.o. (Modules4Presta)
 * @license   https://opensource.org/licenses/MIT MIT License
 *}
<section class="m4p-blog-block m4p-blog-box">
  {if $m4pBlogData.title}<h2 class="m4p-blog-box__title">{$m4pBlogData.title|escape:'html':'UTF-8'}</h2>{/if}
  <ul class="m4p-blog-box__list m4p-blog-box__list--{$m4pBlogSettings.marker|default:'bullet'|escape:'html':'UTF-8'}">
    {foreach from=$m4pBlogData.items item=item}
      <li>{$item|escape:'html':'UTF-8'}</li>
    {/foreach}
  </ul>
</section>
