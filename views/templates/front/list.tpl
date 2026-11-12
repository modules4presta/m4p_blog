{*
 * m4p_blog
 *
 * @author    Modules4Presta <contact@modules4presta.io>
 * @copyright 2026 Nice Code sp. z o.o. (Modules4Presta)
 * @license   https://opensource.org/licenses/MIT MIT License
 *}
{extends file='page.tpl'}

{block name='page_title'}{$m4pBlogTitle}{/block}

{block name='page_content_container'}
  <div id="content" class="m4p-blog">
    {include file='module:m4p_blog/views/templates/front/_partials/categories.tpl'}

    {if $m4pBlogPosts}
      <div class="m4p-blog-grid">
        {foreach from=$m4pBlogPosts item=post}
          {include file='module:m4p_blog/views/templates/front/_partials/post-card.tpl' post=$post}
        {/foreach}
      </div>
      {include file='module:m4p_blog/views/templates/front/_partials/pagination.tpl'}
    {else}
      <p class="m4p-blog-empty">{l s='No posts yet.' d='Modules.M4pblog.Shop'}</p>
    {/if}
  </div>
{/block}
