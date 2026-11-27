{*
 * m4p_blog
 *
 * @author    Modules4Presta <contact@modules4presta.io>
 * @copyright 2026 Nice Code sp. z o.o. (Modules4Presta)
 * @license   https://opensource.org/licenses/MIT MIT License
 *}
<section class="m4p-blog-home"{if $m4pBlogHome.title} aria-labelledby="m4p-blog-home-title"{/if}>
  {if $m4pBlogHome.title}
    <h2 class="m4p-blog-home__title" id="m4p-blog-home-title">{$m4pBlogHome.title|escape:'html':'UTF-8'}</h2>
  {/if}
  <div class="m4p-blog-home__layout{if !$m4pBlogHome.intro_title} m4p-blog-home__layout--posts{/if}">
    {if $m4pBlogHome.intro_title}
      <div class="m4p-blog-home__intro">
        <div>
          <h3 class="m4p-blog-home__intro-title">{$m4pBlogHome.intro_title|escape:'html':'UTF-8'}</h3>
          {if $m4pBlogHome.intro}<p class="m4p-blog-home__intro-text">{$m4pBlogHome.intro|escape:'html':'UTF-8'}</p>{/if}
        </div>
        <a class="m4p-blog-home__all" href="{$m4pBlogHome.all_url|escape:'html':'UTF-8'}">
          {l s='All posts' d='Modules.M4pblog.Shop'}
          <svg class="m4p-blog-home__arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
        </a>
      </div>
    {/if}
    <div class="m4p-blog-home__posts">
      {$m4pBlogHeading = 'h2'}{if $m4pBlogHome.title}{$m4pBlogHeading = 'h3'}{/if}
      {foreach from=$m4pBlogHome.posts item=post}
        {include file='module:m4p_blog/views/templates/front/_partials/post-card.tpl' post=$post headingTag=$m4pBlogHeading imageSizes='(min-width: 1200px) 400px, (min-width: 720px) 40vw, 84vw'}
      {/foreach}
    </div>
  </div>
</section>
