{*
 * m4p_blog
 *
 * @author    Modules4Presta <contact@modules4presta.io>
 * @copyright 2026 Nice Code sp. z o.o. (Modules4Presta)
 * @license   https://opensource.org/licenses/MIT MIT License
 *}
<section class="m4p-blog-block m4p-blog-steps">
  {if $m4pBlogData.title}<h2 class="m4p-blog-steps__title">{$m4pBlogData.title|escape:'html':'UTF-8'}</h2>{/if}
  <ol class="m4p-blog-steps__list">
    {foreach from=$m4pBlogData.steps item=step}
      <li><p>{$step|escape:'html':'UTF-8'}</p></li>
    {/foreach}
  </ol>
</section>
