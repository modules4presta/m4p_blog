{*
 * m4p_blog
 *
 * @author    Modules4Presta <contact@modules4presta.io>
 * @copyright 2026 Nice Code sp. z o.o. (Modules4Presta)
 * @license   https://opensource.org/licenses/MIT MIT License
 *}
{if $m4pBlogCategories}
  <nav class="m4p-blog-categories" aria-label="{l s='Blog categories' d='Modules.M4pblog.Shop'}">
    <ul class="m4p-blog-categories__list">
      {foreach from=$m4pBlogCategories item=category}
        <li class="m4p-blog-categories__item m4p-blog-categories__item--depth-{$category.depth|intval}">
          <a href="{$category.url}">{$category.name}</a>
          {if $category.total}<span class="m4p-blog-categories__count">{$category.total|intval}</span>{/if}
        </li>
      {/foreach}
    </ul>
  </nav>
{/if}
