{*
 * m4p_blog
 *
 * @author    Modules4Presta <contact@modules4presta.io>
 * @copyright 2026 Nice Code sp. z o.o. (Modules4Presta)
 * @license   https://opensource.org/licenses/MIT MIT License
 *}
{$m4pCardHeading = $headingTag|default:'h2'}
<article class="m4p-blog-card">
  {if $post.image}
    <a class="m4p-blog-card__media" href="{$post.url|escape:'html':'UTF-8'}" tabindex="-1" aria-hidden="true">
      <img src="{$post.image.src|escape:'html':'UTF-8'}" srcset="{$post.image.srcset|escape:'html':'UTF-8'}"
           sizes="{$imageSizes|default:'(min-width: 1200px) 380px, (min-width: 768px) 50vw, 100vw'|escape:'html':'UTF-8'}"
           width="{$post.image.width|intval}" height="{$post.image.height|intval}"
           alt="" loading="lazy" decoding="async">
    </a>
  {/if}
  <div class="m4p-blog-card__body">
    {if $post.category}
      <a class="m4p-blog-card__category" href="{$post.category.url|escape:'html':'UTF-8'}">{$post.category.name|escape:'html':'UTF-8'}</a>
    {/if}
    <{$m4pCardHeading} class="m4p-blog-card__title"><a href="{$post.url|escape:'html':'UTF-8'}">{$post.title|escape:'html':'UTF-8'}</a></{$m4pCardHeading}>
    {if $post.excerpt}<p class="m4p-blog-card__excerpt">{$post.excerpt|escape:'html':'UTF-8'}</p>{/if}
    {if $post.author || $post.reading_time}
      <p class="m4p-blog-card__meta">
        {if $post.author}
          <span class="m4p-blog-card__author">{$post.author.display_name|escape:'html':'UTF-8'}</span>
        {/if}
        {if $post.reading_time}
          <span class="m4p-blog-card__reading">{l s='%count% min read' sprintf=['%count%' => $post.reading_time|intval] d='Modules.M4pblog.Shop'}</span>
        {/if}
      </p>
    {/if}
  </div>
</article>
