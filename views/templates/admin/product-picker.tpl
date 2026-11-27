{*
 * m4p_blog
 *
 * @author    Modules4Presta <contact@modules4presta.io>
 * @copyright 2026 Nice Code sp. z o.o. (Modules4Presta)
 * @license   https://opensource.org/licenses/MIT MIT License
 *}
<div class="m4p-blog-picker" data-m4p-blog-picker data-search-url="{$m4pBlogPicker.search_url|escape:'html':'UTF-8'}"
  data-products="{$m4pBlogPicker.products|escape:'html':'UTF-8'}"
  data-label-up="{l s='Move up' d='Modules.M4pblog.Admin'}"
  data-label-down="{l s='Move down' d='Modules.M4pblog.Admin'}"
  data-label-remove="{l s='Remove' d='Admin.Actions'}"
  data-label-none="{l s='No product found. Check the name or reference.' d='Modules.M4pblog.Admin'}">
  <input type="hidden" name="products" value="{$m4pBlogPicker.value|escape:'html':'UTF-8'}" data-m4p-blog-picker-value>
  <ol class="m4p-blog-picker__list" data-m4p-blog-picker-list></ol>
  <div class="m4p-blog-picker__search">
    <input type="search" class="form-control" autocomplete="off" data-m4p-blog-picker-search
      placeholder="{l s='Search by name or reference' d='Modules.M4pblog.Admin'}"
      aria-label="{l s='Search by name or reference' d='Modules.M4pblog.Admin'}">
    <ul class="m4p-blog-picker__results" data-m4p-blog-picker-results hidden></ul>
  </div>
  <p class="help-block">{l s='Shown under the post in this order, as product cards. No products: no section.' d='Modules.M4pblog.Admin'}</p>
</div>
