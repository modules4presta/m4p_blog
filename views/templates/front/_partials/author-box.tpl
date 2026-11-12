{*
 * m4p_blog
 *
 * @author    Modules4Presta <contact@modules4presta.io>
 * @copyright 2026 Nice Code sp. z o.o. (Modules4Presta)
 * @license   https://opensource.org/licenses/MIT MIT License
 *}
<aside class="m4p-blog-author">
  {if $author.image}
    <img class="m4p-blog-author__photo" src="{$author.image.src}" srcset="{$author.image.srcset}"
         sizes="96px" width="{$author.image.width}" height="{$author.image.height}"
         alt="{$author.display_name}" loading="lazy" decoding="async">
  {/if}
  <div class="m4p-blog-author__body">
    <p class="m4p-blog-author__name">
      {if $linked}<a href="{$author.url}">{$author.display_name}</a>{else}{$author.display_name}{/if}
    </p>
    {if $author.role}<p class="m4p-blog-author__role">{$author.role}</p>{/if}
    {if $author.bio}<div class="m4p-blog-author__bio">{$author.bio nofilter}</div>{/if}
    {if $author.links}
      <ul class="m4p-blog-author__links">
        {foreach from=$author.links item=link}
          <li><a href="{$link.url}" rel="nofollow noopener" target="_blank">{$link.label}</a></li>
        {/foreach}
      </ul>
    {/if}
  </div>
</aside>
