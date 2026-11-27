{*
 * m4p_blog
 *
 * @author    Modules4Presta <contact@modules4presta.io>
 * @copyright 2026 Nice Code sp. z o.o. (Modules4Presta)
 * @license   https://opensource.org/licenses/MIT MIT License
 *}
<div class="m4p-empty m4p-blog-empty">
  <span class="m4p-empty__icon">
    <svg class="m4p-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M10 9H8"/><path d="M16 13H8"/><path d="M16 17H8"/></svg>
  </span>
  <p class="m4p-empty__title">{$emptyTitle|regex_replace:'/\.$/':''|escape:'html':'UTF-8'}</p>
  {if !empty($emptyAllPosts)}
    <div class="m4p-empty__actions">
      <a class="btn btn-primary" href="{$link->getModuleLink('m4p_blog', 'list')|escape:'html':'UTF-8'}">{l s='All posts' d='Modules.M4pblog.Shop'}</a>
    </div>
  {/if}
</div>
