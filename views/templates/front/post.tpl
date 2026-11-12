{*
 * m4p_blog
 *
 * @author    Modules4Presta <contact@modules4presta.io>
 * @copyright 2026 Nice Code sp. z o.o. (Modules4Presta)
 * @license   https://opensource.org/licenses/MIT MIT License
 *}
{extends file='page.tpl'}

{block name='page_title'}{$m4pBlogPost.title}{/block}

{block name='page_content_container'}
  <article id="content" class="m4p-blog m4p-blog-post">
    {hook h='displayM4pBlogJsonLd' entity='post' id_post=$m4pBlogPost.id url=$m4pBlogPost.url}

    <p class="m4p-blog-post__meta">
      <time datetime="{$m4pBlogPost.date_published|date_format:'%Y-%m-%d'}">
        {$m4pBlogPost.date_published|date_format:'%d.%m.%Y'}
      </time>
      {if $m4pBlogPost.category}
        <a class="m4p-blog-post__category" href="{$m4pBlogPost.category.url}">{$m4pBlogPost.category.name}</a>
      {/if}
      {if $m4pBlogPost.author}
        <a class="m4p-blog-post__author" href="{$m4pBlogPost.author.url}">{$m4pBlogPost.author.display_name}</a>
      {/if}
      {if $m4pBlogPost.reading_time}
        <span class="m4p-blog-post__reading">
          {l s='%count% min' sprintf=['%count%' => $m4pBlogPost.reading_time] d='Modules.M4pblog.Shop'}
        </span>
      {/if}
    </p>

    {if $m4pBlogPost.image}
      <img class="m4p-blog-cover" src="{$m4pBlogPost.image.src}" srcset="{$m4pBlogPost.image.srcset}"
           sizes="(min-width: 1200px) 820px, 100vw"
           width="{$m4pBlogPost.image.width}" height="{$m4pBlogPost.image.height}"
           alt="{$m4pBlogPost.title}" decoding="async">
    {/if}

    {if $m4pBlogPost.excerpt}
      <p class="m4p-blog-post__lead">{$m4pBlogPost.excerpt}</p>
    {/if}

    <div class="m4p-blog-post__content">{$m4pBlogContent nofilter}</div>

    {hook h='displayM4pBlogPostAfterContent' id_post=$m4pBlogPost.id}

    {if $m4pBlogPost.author}
      {include file='module:m4p_blog/views/templates/front/_partials/author-box.tpl' author=$m4pBlogPost.author linked=true}
    {/if}
  </article>
{/block}
