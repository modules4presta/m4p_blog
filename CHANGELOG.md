# Changelog

All notable changes to this project are documented here. The format follows
[Keep a Changelog](https://keepachangelog.com/en/1.1.0/) and the project uses
[semantic versioning](https://semver.org/).

## [1.3.0] - 2026-11-26

### Added
- Content blocks next to Text: Parameters ("Label: value" rows), Boxed list (title, items, bullets or check
  marks), Steps (title, numbered steps), Highlight (information, tip or warning in the theme's `.m4p-msg`
  component), Questions and answers (an accordion of `<details>`), File to download (a PDF up to 20 MB in
  `img/m4p_blog/files/`, with its size). The editor writes the titles; the names are not tied to one trade.
- Products of a post: chosen in the post form by name or reference, in their order, shown under the post
  as the theme's product card (`catalog/_partials/miniatures/product.tpl`); a section title per language,
  "Products from this post" when empty. Disabled or hidden products are left out; no products, no section.
- "More from the blog": up to three latest posts of the post's main category, without the post itself, as
  the post card; none, no section.
- A changed friendly URL of a post, a category or an author is remembered; the former address answers with
  a 301 to the current one. Taking an address back serves it again.

### Changed
- Post page in one reading column: category, title, excerpt as the lead, `author | date | reading time`,
  cover (`fetchpriority="high"`), content, author box, products, more from the blog, link to all posts.

## [1.2.0] - 2026-11-25

### Added
- Blog page introduction: a text per language under the title of the blog page, in the module configuration.
- Template variables for a category menu with an "All" entry: `m4pBlogTotal` (published posts of the blog),
  `m4pBlogCategoryId` (the open category, 0 on the blog page) and `has_children` on every category.
- `date` on a presented post: the publication date in the shop's format.

### Changed
- A blog category page takes the layout set for `module-m4p_blog-category`, not the one of the shop's
  product category page.

## [1.1.0] - 2026-11-19

### Added
- Home page block on `displayHome`: the latest posts (two by default, 0 hides the block), from the
  whole blog or one category, each as the post card, and an
  optional introduction panel with a title, a text and a link to all posts. Settings in the module
  configuration, texts per language.

### Changed
- Numbered pagination (first and last page, the current one with its neighbours, arrows) in the markup of
  the theme's pagination (`.pagination`, `.page-item`, `.page-link`); the module's own styles apply only when
  the theme has none.
- Post card: photo 16:10, category, title, one-sentence excerpt and `author | reading time`. An empty list, category or author page shows an empty state with an icon and a
  link to all posts (the theme's `.m4p-empty`, with a base layout of its own).

## [1.0.1] - 2026-10-28

### Changed
- The canonical address of a blog page is the one the module builds, so a friendly address is compared
  against itself.

## [1.0.0] - 2026-10-28

### Added
- Posts, categories and authors of a shop blog, each table keyed by `id_shop`.
- Post content as an ordered list of typed blocks stored as JSON, with one type in this release: `html`,
  edited with the standard PrestaShop editor and cleaned against an allow-list on save.
- Back office under one menu entry: Posts, Categories and Authors, with bulk publish and hide on posts and
  drag ordering on categories.
- Four front office pages with friendly URLs: `/blog`, `/blog/category/<url>`, `/blog/author/<url>` and
  `/blog/<url>`, with their own templates, canonical addresses and breadcrumbs.
- Cover and photo images stored as WebP copies in three widths with their dimensions, for `srcset`,
  `width` and `height` in every `<img>`.
- Module settings: posts per page and whether the blog is visible.
- Hooks `displayM4pBlogJsonLd` and `displayM4pBlogPostAfterContent`.
- Tables for tags and comments, created at install and not used yet.
- English and Polish translation catalogues.
