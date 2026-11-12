<?php

/**
 * m4p_blog
 *
 * @author    Modules4Presta <contact@modules4presta.io>
 * @copyright 2026 Nice Code sp. z o.o. (Modules4Presta)
 * @license   https://opensource.org/licenses/MIT MIT License
 */

declare(strict_types=1);

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Allow-list cleaner for the rich text of content blocks: structure stays, inline styles, classes, scripts
 * and pasted markup (Word, other editors) go. Unknown elements are unwrapped, so their text is kept.
 * The post title is the only H1 of the page, so headings in the content start at H2.
 */
class M4pBlogHtml
{
    private const ALLOWED = [
        'p', 'br', 'hr', 'h2', 'h3', 'h4', 'strong', 'b', 'em', 'i', 'u', 's',
        'ul', 'ol', 'li', 'a', 'blockquote', 'figure', 'figcaption', 'img',
        'table', 'thead', 'tbody', 'tfoot', 'tr', 'th', 'td', 'sub', 'sup', 'code', 'pre',
    ];
    private const DROPPED = [
        'script', 'style', 'iframe', 'object', 'embed', 'form', 'input', 'button',
        'select', 'textarea', 'svg', 'video', 'audio', 'head', 'meta', 'link',
    ];
    private const KEPT_ATTRIBUTES = [
        'a' => ['href', 'target', 'rel'],
        'img' => ['src', 'alt', 'width', 'height'],
        'th' => ['colspan', 'rowspan', 'scope'],
        'td' => ['colspan', 'rowspan'],
    ];
    /** Void elements libxml does not know: it would treat everything after them as their content. */
    private const VOID_UNKNOWN = '#<(embed|source|track|param|area|base|col|keygen|wbr)\b[^>]*>#i';
    private const SAFE_URL = '#^(https?://|mailto:|tel:|/(?!/)|\#)#i';
    private const SAFE_IMAGE_URL = '#^(https?://|/(?!/)|data:image/)#i';

    public static function clean(string $html): string
    {
        $html = trim($html);
        if ($html === '') {
            return '';
        }

        $html = preg_replace(self::VOID_UNKNOWN, '', $html);
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML(
            '<?xml encoding="UTF-8"><div id="m4p-root">' . $html . '</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();
        $root = $dom->getElementById('m4p-root');
        if ($root === null) {
            return '';
        }
        self::cleanChildren($root);

        $out = '';
        foreach ($root->childNodes as $child) {
            $out .= $dom->saveHTML($child);
        }
        $out = preg_replace('/<p>(\s|&nbsp;|\x{a0}|<br>)*<\/p>/u', '', $out);
        $out = trim((string) $out);

        return strip_tags($out, '<img><hr>') === '' && strpos($out, '<img') === false ? '' : $out;
    }

    /**
     * Readable text of the markup, for excerpts and the reading time.
     */
    public static function toText(string $html): string
    {
        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim((string) preg_replace('/\s+/u', ' ', $text));
    }

    private static function cleanChildren(DOMNode $node): void
    {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child instanceof DOMText && !$child instanceof DOMCdataSection) {
                continue;
            }
            if (!$child instanceof DOMElement) {
                $node->removeChild($child);
                continue;
            }

            $name = strtolower($child->nodeName);
            self::cleanChildren($child);

            if (in_array($name, self::DROPPED, true)) {
                $node->removeChild($child);
                continue;
            }
            if ($name === 'h1') {
                $child = self::rename($child, 'h2');
                $name = 'h2';
            } elseif (in_array($name, ['h5', 'h6'], true)) {
                $child = self::rename($child, 'h4');
                $name = 'h4';
            } elseif ($name === 'div') {
                // A div holding blocks only wraps them; a div of text is a paragraph.
                $hasBlocks = false;
                foreach ($child->childNodes as $grandchild) {
                    $hasBlocks = $hasBlocks || in_array(
                        strtolower($grandchild->nodeName),
                        ['p', 'h2', 'h3', 'h4', 'ul', 'ol', 'table', 'blockquote', 'figure'],
                        true
                    );
                }
                if (!$hasBlocks) {
                    $child = self::rename($child, 'p');
                    $name = 'p';
                }
            }

            if (!in_array($name, self::ALLOWED, true)) {
                self::unwrap($node, $child);
                continue;
            }

            self::cleanAttributes($child, $name);
            if (($name === 'a' && !$child->hasAttribute('href'))
                || ($name === 'img' && !$child->hasAttribute('src'))
            ) {
                $name === 'img' ? $node->removeChild($child) : self::unwrap($node, $child);
            }
        }
    }

    private static function cleanAttributes(DOMElement $element, string $name): void
    {
        $kept = [];
        foreach (self::KEPT_ATTRIBUTES[$name] ?? [] as $attribute) {
            $value = trim($element->getAttribute($attribute));
            if ($value !== '') {
                $kept[$attribute] = $value;
            }
        }
        foreach (iterator_to_array($element->attributes) as $attribute) {
            $element->removeAttribute($attribute->nodeName);
        }

        if ($name === 'a' && isset($kept['href']) && preg_match(self::SAFE_URL, $kept['href'])) {
            $element->setAttribute('href', $kept['href']);
            if (($kept['target'] ?? '') === '_blank') {
                $element->setAttribute('target', '_blank');
                $element->setAttribute('rel', 'noopener');
            } elseif (isset($kept['rel']) && $kept['rel'] === 'nofollow') {
                $element->setAttribute('rel', 'nofollow');
            }

            return;
        }

        if ($name === 'img' && isset($kept['src']) && preg_match(self::SAFE_IMAGE_URL, $kept['src'])) {
            $element->setAttribute('src', $kept['src']);
            $element->setAttribute('alt', $kept['alt'] ?? '');
            foreach (['width', 'height'] as $dimension) {
                if (isset($kept[$dimension]) && ctype_digit($kept[$dimension])) {
                    $element->setAttribute($dimension, $kept[$dimension]);
                }
            }
            $element->setAttribute('loading', 'lazy');
            $element->setAttribute('decoding', 'async');

            return;
        }

        foreach (['colspan', 'rowspan'] as $span) {
            if (isset($kept[$span]) && ctype_digit($kept[$span])) {
                $element->setAttribute($span, $kept[$span]);
            }
        }
        if ($name === 'th' && in_array($kept['scope'] ?? '', ['col', 'row'], true)) {
            $element->setAttribute('scope', $kept['scope']);
        }
    }

    private static function unwrap(DOMNode $parent, DOMElement $element): void
    {
        while ($element->firstChild) {
            $parent->insertBefore($element->firstChild, $element);
        }
        $parent->removeChild($element);
    }

    private static function rename(DOMElement $element, string $name): DOMElement
    {
        $renamed = $element->ownerDocument->createElement($name);
        while ($element->firstChild) {
            $renamed->appendChild($element->firstChild);
        }
        $element->parentNode->replaceChild($renamed, $element);

        return $renamed;
    }
}
