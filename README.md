# Blog for PrestaShop 8 & 9

**A blog that lives inside the shop: posts built from content blocks, categories and authors edited in the back office, pages that load fast and keep the shop's look.**

> **Meta description (155 chars):** Blog module for PrestaShop: posts from content blocks, categories, authors, friendly URLs and WebP images. Multistore. Free, MIT.

---

## Why a blog in the shop matters for your store

Articles bring search traffic that product pages do not: buying guides, comparisons and answers to
the questions customers ask before they order. When the blog runs on a separate platform, that
traffic lands on another domain or subdomain, links to products are typed by hand, and the look
drifts away from the shop.

A blog inside PrestaShop keeps the articles on the shop's own addresses, next to the products they
are about, in the same templates and languages.

- **Traffic on the shop's domain** – every post is a page of the shop, with its own friendly URL and meta fields
- **One back office** – posts, categories and authors are edited where the products are
- **Fast pages** – images are served as WebP in three widths with their dimensions, so they do not shift the layout

## What the module does

The module adds a blog to the front office (a list of posts, category pages, author pages and post
pages), a block of the latest posts on the home page and three back office screens to manage it. The content of a post is an ordered list of
typed blocks stored as JSON, not one HTML field. The front office ships its own templates and takes
colours and fonts from the theme.

### Key features

- **Posts** – status (draft, scheduled, published), publication date, author, main category, reading time, cover image, per-language meta fields and friendly URL
- **Content as blocks** – text (the standard PrestaShop editor), parameters, a boxed list, steps, a highlight (information, tip, warning), questions and answers and a PDF to download; titles are written by the editor, so the blocks fit any trade, not only recipes. Further types are added as block classes and need no change to stored content
- **Products under a post** – chosen by name or reference, in their order, shown as the theme's product card, with a section title per language
- **More from the blog** – up to three latest posts of the same main category under a post
- **Categories** – a tree up to three levels, each with an image, a description and meta fields
- **Authors** – display name, photo, role, bio and links, optionally tied to a back office account
- **Four front office pages** with friendly URLs: the blog, a category, an author and a post
- **Home page block** – the latest posts on `displayHome`, from the whole blog or one category, with an optional introduction panel and a link to all posts
- **WebP images** – three widths (480, 960 and 1600 px) with their dimensions, so every `<img>` carries `srcset`, `width` and `height`
- **Clean markup** – the rich text of a block is cleaned against an allow-list on save, and `h1` becomes `h2`, because the post title is the only `h1` of the page
- **Multistore** – every table carries `id_shop` in its primary key, so a post, a category and an author belong to one shop

### Not part of this module

- **Structured data.** The post page calls the `displayM4pBlogJsonLd` hook; `m4p_jsonld` answers it.
- **Colours and fonts.** The front office stylesheet holds layout only and reads the theme's tokens.
- **Newsletter, sharing buttons, view counters and a captcha.**
- **Tags, comments, a usefulness rating, related products and import from another blog module.** These are prepared, not built yet: the `m4p_blog_tag`, `m4p_blog_post_tag` and `m4p_blog_comment` tables are created at install and stay empty.

## Compatibility

| | |
|---|---|
| PrestaShop | 8.0 – 9.x |
| PHP | 7.4+ (developed and tested on 8.1), GD with WebP support |
| Requirements | friendly URLs switched on |
| Multistore | Compatible – posts, categories and authors per shop |
| Themes | Works with any theme – templates ship in the module and a theme can override them |

The module performs no core overrides. It adds nine database tables, seven settings and a **Blog**
menu with three tabs in the back office.

## Installation

1. Upload and install the module from **Modules → Module Manager**.
2. In the module configuration, set the number of posts per page and whether the blog is visible.
3. Add authors, then categories, then posts in **Blog** in the back office menu.
4. Open `/blog` in the shop.

If the blog pages answer 404, check that **Shop parameters → Traffic & SEO → Friendly URL** is on
and that **Blog visible** is on in the module configuration.

## Configuration options

| Setting | Description |
|---|---|
| **Blog visible** | On by default. Off: the blog pages answer 404 and the posts stay in the back office. |
| **Posts per page** | 9 by default. Posts on the blog page and on a category or author page. |
| **Blog page introduction** | Per language. A text under the title of the blog page; empty hides it. |
| **Posts on the home page** | 2 by default. How many of the latest posts the home page block shows; 0 hides it. |
| **Home page posts from** | The whole blog or one category. |
| **Home page block title, introduction title and text** | Per language. Without an introduction title the block shows the posts only. |

## Addresses

| Page | Address |
| --- | --- |
| Blog | `/blog` |
| Category | `/blog/category/<friendly-url>` |
| Author | `/blog/author/<friendly-url>` |
| Post | `/blog/<friendly-url>` |

Module routes are the same in every language of a shop, so the segments are the constants
`ROUTE_BASE`, `ROUTE_CATEGORY` and `ROUTE_AUTHOR` in `m4p_blog.php`. Changing them changes every
address the module builds; existing addresses then need a 301 in the server configuration.

A friendly URL changed in the back office (post, category or author) is kept in `m4p_blog_redirect`;
the former address answers with a 301 to the current one.

## Content blocks

`m4p_blog_post_lang.content` holds a JSON list. One block is:

```json
{
  "type": "html",
  "active": true,
  "settings": {},
  "data": { "body": "<p>The text of the post.</p>" }
}
```

`data` holds the fields a block type marks translatable, `settings` the rest; both sit inside the
language row, because the content itself is per language. The shape is the one `m4p_productcontent`
uses, so block types and their templates carry over between the two modules.

| Block | Fields |
| --- | --- |
| Text (`html`) | Rich text |
| Parameters (`facts`) | Rows `Label: value`, shown as a bar of up to four columns |
| Boxed list (`list`) | Title, items one per line, bullets or check marks |
| Steps (`steps`) | Title, steps one per line, numbered |
| Highlight (`note`) | Information, tip or warning, and the text; rendered in the `.m4p-msg` component |
| Questions and answers (`faq`) | Title, pairs: the question in the first line, the answer below, an empty line between pairs |
| File to download (`file`) | A PDF up to 20 MB (stored in `img/m4p_blog/files/`) and its name |

The module renders no structured data for questions and answers; a module on `displayM4pBlogJsonLd`
can add it.

A new block type is a class in `classes/Block/` extending `M4pBlogBlock`: it names its type, its
label and icon, declares its fields, and ships a template in
`views/templates/front/blocks/<type>.tpl`. Adding it to `M4pBlogBlocks::TYPES` puts it in the
editor's picker.

The allow-list keeps structure and drops inline styles, classes, scripts and pasted markup. `h5` and
`h6` become `h4`. Images keep `src`, `alt`, `width` and `height` and always get `loading="lazy"`.

## Theming

A theme overrides the templates in `themes/<theme>/modules/m4p_blog/views/templates/front/`. The
class names are stable (`m4p-blog-card`, `m4p-blog-post`, `m4p-blog-author`, …) and
`views/css/front.css` only lays them out, reading `--spacing-*`, `--fs-*` and `--r-*` with neutral
fallbacks.

Two hooks are available on the post page: `displayM4pBlogPostAfterContent` below the content and
`displayM4pBlogJsonLd` for structured data.

## Frequently asked questions

**Can I move posts from another blog module?**
Not yet. The import is prepared in the data model but not built; posts are added in the back office.

**Does it add structured data for articles?**
No. The post page raises `displayM4pBlogJsonLd`, so one module emits all structured data of the
shop; `m4p_jsonld` answers that hook.

**Will it look like my theme?**
Yes, as far as colours and fonts go: the module stylesheet sets layout only and reads the theme's
CSS custom properties. Markup changes go into template overrides in the theme.

**Does it work with several shops in one installation?**
Yes. Posts, categories and authors belong to the shop selected in the back office, and every shop
has its own blog.

**What happens when I uninstall the module?**
Its tables and settings are deleted, so posts, categories and authors go with them. Image files
stay in `img/m4p_blog/`. Export what you need before uninstalling.

---

**Keywords:** PrestaShop blog module, blog for PrestaShop 8, PrestaShop 9 blog, content marketing PrestaShop, PrestaShop articles, SEO blog PrestaShop, multistore blog PrestaShop.

## Translations

English is the source language in the code, Polish lives in the catalogues under `translations/`.
The back office uses the domain `Modules.M4pblog.Admin`, the front office `Modules.M4pblog.Shop`.

## Coding standards

```bash
composer install
composer phpcs
composer cs-check
```

## License

MIT – see [LICENSE](LICENSE). Free to use commercially, fork and modify; keep the copyright notice.

## Contributing

Bug reports and pull requests are welcome – see [CONTRIBUTING.md](CONTRIBUTING.md). For security
issues, follow [SECURITY.md](SECURITY.md) instead of opening a public issue.

---

Built by [Nice Code](https://nice-code.com/pl/produkty/sklep-b2b-prestashop) – we build and maintain PrestaShop stores.

© Nice Code sp. z o.o. (Modules4Presta) – released under the MIT license.
