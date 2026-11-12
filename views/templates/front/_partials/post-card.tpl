{*
 * m4p_blog
 *
 * @author    Modules4Presta <contact@modules4presta.io>
 * @copyright 2026 Nice Code sp. z o.o. (Modules4Presta)
 * @license   https://opensource.org/licenses/MIT MIT License
 *}
<article class="m4p-blog-card">
  {if $post.image}
    <a class="m4p-blog-card__media" href="{$post.url}">
      <img src="{$post.image.src}" srcset="{$post.image.srcset}"
           sizes="(min-width: 1200px) 380px, (min-width: 768px) 50vw, 100vw"
           width="{$post.image.width}" height="{$post.image.height}"
           alt="{$post.title}" loading="lazy" decoding="async">
    </a>
  {/if}
  <div class="m4p-blog-card__body">
    {if $post.category}
      <a class="m4p-blog-card__category" href="{$post.category.url}">{$post.category.name}</a>
    {/if}
    <h2 class="m4p-blog-card__title"><a href="{$post.url}">{$post.title}</a></h2>
    {if $post.excerpt}<p class="m4p-blog-card__excerpt">{$post.excerpt}</p>{/if}
    <p class="m4p-blog-card__meta">
      <time datetime="{$post.date_published|date_format:'%Y-%m-%d'}">
        {$post.date_published|date_format:'%d.%m.%Y'}
      </time>
      {if $post.author}
        <span class="m4p-blog-card__author">{$post.author.display_name}</span>
      {/if}
      {if $post.reading_time}
        <span class="m4p-blog-card__reading">
          {l s='%count% min' sprintf=['%count%' => $post.reading_time] d='Modules.M4pblog.Shop'}
        </span>
      {/if}
    </p>
  </div>
</article>
