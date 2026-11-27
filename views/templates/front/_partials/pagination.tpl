{*
 * m4p_blog
 *
 * @author    Modules4Presta <contact@modules4presta.io>
 * @copyright 2026 Nice Code sp. z o.o. (Modules4Presta)
 * @license   https://opensource.org/licenses/MIT MIT License
 *}
{if $m4pBlogPagination.pages > 1}
  <nav class="m4p-blog-pagination" aria-label="{l s='Pagination' d='Modules.M4pblog.Shop'}">
    <ul class="pagination">
      <li class="page-item{if !$m4pBlogPagination.previous} disabled{/if}">
        {if $m4pBlogPagination.previous}
          <a class="page-link previous" rel="prev" href="{$m4pBlogPagination.previous|escape:'html':'UTF-8'}" aria-label="{l s='Go to previous page' d='Shop.Theme.Actions'}">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" focusable="false"><path d="M15 6l-6 6 6 6"></path></svg>
          </a>
        {else}
          <span class="page-link previous disabled" aria-hidden="true">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" focusable="false"><path d="M15 6l-6 6 6 6"></path></svg>
          </span>
        {/if}
      </li>
      {foreach from=$m4pBlogPagination.links item=link}
        {if $link.page === null}
          <li class="page-item disabled"><span class="page-link" aria-hidden="true">&hellip;</span></li>
        {elseif $link.current}
          <li class="page-item active"><span class="page-link" aria-current="page">{$link.page|intval}</span></li>
        {else}
          <li class="page-item">
            <a class="page-link" href="{$link.url|escape:'html':'UTF-8'}" aria-label="{l s='Go to page %page%' sprintf=['%page%' => $link.page|intval] d='Shop.Theme.Actions'}">{$link.page|intval}</a>
          </li>
        {/if}
      {/foreach}
      <li class="page-item{if !$m4pBlogPagination.next} disabled{/if}">
        {if $m4pBlogPagination.next}
          <a class="page-link next" rel="next" href="{$m4pBlogPagination.next|escape:'html':'UTF-8'}" aria-label="{l s='Go to next page' d='Shop.Theme.Actions'}">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" focusable="false"><path d="M9 6l6 6-6 6"></path></svg>
          </a>
        {else}
          <span class="page-link next disabled" aria-hidden="true">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" focusable="false"><path d="M9 6l6 6-6 6"></path></svg>
          </span>
        {/if}
      </li>
    </ul>
  </nav>
{/if}
