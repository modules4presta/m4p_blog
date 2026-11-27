{*
 * m4p_blog
 *
 * @author    Modules4Presta <contact@modules4presta.io>
 * @copyright 2026 Nice Code sp. z o.o. (Modules4Presta)
 * @license   https://opensource.org/licenses/MIT MIT License
 *}
<p class="m4p-blog-post__meta">
  {if $post.author}
    <a class="m4p-blog-post__author" href="{$post.author.url|escape:'html':'UTF-8'}">{$post.author.display_name|escape:'html':'UTF-8'}</a>
  {/if}
  <time datetime="{$post.date_published|date_format:'%Y-%m-%d'}">{$post.date|escape:'html':'UTF-8'}</time>
  {if $post.reading_time}
    <span class="m4p-blog-post__reading">{l s='%count% min read' sprintf=['%count%' => $post.reading_time|intval] d='Modules.M4pblog.Shop'}</span>
  {/if}
</p>
