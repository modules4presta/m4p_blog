{*
 * m4p_blog
 *
 * @author    Modules4Presta <contact@modules4presta.io>
 * @copyright 2026 Nice Code sp. z o.o. (Modules4Presta)
 * @license   https://opensource.org/licenses/MIT MIT License
 *}
{if $m4pBlogProducts}
  <section class="m4p-blog-related m4p-blog-products" aria-labelledby="m4p-blog-products-title">
    <div class="m4p-blog-related__head">
      <h2 class="m4p-blog-related__title" id="m4p-blog-products-title">{$m4pBlogProductsTitle|escape:'html':'UTF-8'}</h2>
      <a class="m4p-blog-related__more" href="{$m4pBlogCatalogUrl|escape:'html':'UTF-8'}">{l s='Whole catalogue' d='Modules.M4pblog.Shop'}</a>
    </div>
    <div class="m4p-blog-products__list">
      {foreach from=$m4pBlogProducts item=product}
        <div class="m4p-blog-products__item">
          {include file='catalog/_partials/miniatures/product.tpl' product=$product}
        </div>
      {/foreach}
    </div>
  </section>
{/if}
