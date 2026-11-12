# Changelog

All notable changes to this project are documented here. The format follows
[Keep a Changelog](https://keepachangelog.com/en/1.1.0/) and the project uses
[semantic versioning](https://semver.org/).

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
