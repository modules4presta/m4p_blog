{*
 * m4p_blog
 *
 * @author    Modules4Presta <contact@modules4presta.io>
 * @copyright 2026 Nice Code sp. z o.o. (Modules4Presta)
 * @license   https://opensource.org/licenses/MIT MIT License
 *}
{if $m4pBlogPagination.pages > 1}
  <nav class="m4p-blog-pagination" aria-label="{l s='Pagination' d='Modules.M4pblog.Shop'}">
    {if $m4pBlogPagination.previous}
      <a class="m4p-blog-pagination__link" rel="prev" href="{$m4pBlogPagination.previous}">
        {l s='Previous' d='Shop.Theme.Actions'}
      </a>
    {/if}
    <span class="m4p-blog-pagination__state">
      {l s='Page %current% of %total%' sprintf=['%current%' => $m4pBlogPagination.current, '%total%' => $m4pBlogPagination.pages] d='Modules.M4pblog.Shop'}
    </span>
    {if $m4pBlogPagination.next}
      <a class="m4p-blog-pagination__link" rel="next" href="{$m4pBlogPagination.next}">
        {l s='Next' d='Shop.Theme.Actions'}
      </a>
    {/if}
  </nav>
{/if}
