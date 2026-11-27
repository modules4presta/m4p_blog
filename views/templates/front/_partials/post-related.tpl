{*
 * m4p_blog
 *
 * @author    Modules4Presta <contact@modules4presta.io>
 * @copyright 2026 Nice Code sp. z o.o. (Modules4Presta)
 * @license   https://opensource.org/licenses/MIT MIT License
 *}
{if $m4pBlogRelated}
  <section class="m4p-blog-related m4p-blog-more" aria-labelledby="m4p-blog-more-title">
    <div class="m4p-blog-related__head">
      <h2 class="m4p-blog-related__title" id="m4p-blog-more-title">{l s='More from the blog' d='Modules.M4pblog.Shop'}</h2>
    </div>
    <div class="m4p-blog-more__list">
      {foreach from=$m4pBlogRelated item=post}
        {include file='module:m4p_blog/views/templates/front/_partials/post-card.tpl' post=$post headingTag='h3' imageSizes='(min-width: 1200px) 400px, (min-width: 768px) 50vw, 100vw'}
      {/foreach}
    </div>
  </section>
{/if}
