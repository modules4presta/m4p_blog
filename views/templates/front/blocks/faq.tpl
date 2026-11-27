{*
 * m4p_blog
 *
 * @author    Modules4Presta <contact@modules4presta.io>
 * @copyright 2026 Nice Code sp. z o.o. (Modules4Presta)
 * @license   https://opensource.org/licenses/MIT MIT License
 *}
<section class="m4p-blog-block m4p-blog-faq">
  {if $m4pBlogData.title}<h2 class="m4p-blog-faq__title">{$m4pBlogData.title|escape:'html':'UTF-8'}</h2>{/if}
  {foreach from=$m4pBlogData.items item=pair}
    <details class="m4p-blog-faq__item">
      <summary class="m4p-blog-faq__question">{$pair.q|escape:'html':'UTF-8'}</summary>
      <p class="m4p-blog-faq__answer">{$pair.a|escape:'html':'UTF-8'|nl2br nofilter}</p>
    </details>
  {/foreach}
</section>
