{*
 * m4p_blog
 *
 * @author    Modules4Presta <contact@modules4presta.io>
 * @copyright 2026 Nice Code sp. z o.o. (Modules4Presta)
 * @license   https://opensource.org/licenses/MIT MIT License
 *}
{extends file='page.tpl'}

{block name='page_title'}{$m4pBlogPost.title}{/block}
{block name='page_header_container'}{/block}

{block name='page_content_container'}
  <article id="content" class="m4p-blog m4p-blog-post">
    {hook h='displayM4pBlogJsonLd' entity='post' id_post=$m4pBlogPost.id url=$m4pBlogPost.url}

    <header class="m4p-blog-post__head">
      {if $m4pBlogPost.category}
        <a class="m4p-blog-post__category" href="{$m4pBlogPost.category.url|escape:'html':'UTF-8'}">{$m4pBlogPost.category.name|escape:'html':'UTF-8'}</a>
      {/if}
      <h1 class="m4p-blog-post__title">{$m4pBlogPost.title|escape:'html':'UTF-8'}</h1>
      {if $m4pBlogPost.excerpt}
        <p class="m4p-blog-post__lead">{$m4pBlogPost.excerpt|escape:'html':'UTF-8'}</p>
      {/if}
      {include file='module:m4p_blog/views/templates/front/_partials/post-meta.tpl' post=$m4pBlogPost}
    </header>

    {if $m4pBlogPost.image}
      <figure class="m4p-blog-post__cover">
        <img class="m4p-blog-cover" src="{$m4pBlogPost.image.src|escape:'html':'UTF-8'}" srcset="{$m4pBlogPost.image.srcset|escape:'html':'UTF-8'}"
             sizes="(min-width: 1064px) 1000px, 100vw"
             width="{$m4pBlogPost.image.width|intval}" height="{$m4pBlogPost.image.height|intval}"
             alt="{$m4pBlogPost.title|escape:'html':'UTF-8'}" fetchpriority="high" decoding="async">
      </figure>
    {/if}

    <div class="m4p-blog-post__content">{$m4pBlogContent nofilter}</div>

    {hook h='displayM4pBlogPostAfterContent' id_post=$m4pBlogPost.id}

    {if $m4pBlogPost.author}
      {include file='module:m4p_blog/views/templates/front/_partials/author-box.tpl' author=$m4pBlogPost.author linked=true}
    {/if}

    {include file='module:m4p_blog/views/templates/front/_partials/post-products.tpl'}
    {include file='module:m4p_blog/views/templates/front/_partials/post-related.tpl'}

    <a class="m4p-blog-post__back" href="{$m4pBlogUrl|escape:'html':'UTF-8'}">{l s='All posts' d='Modules.M4pblog.Shop'}</a>
  </article>
{/block}
