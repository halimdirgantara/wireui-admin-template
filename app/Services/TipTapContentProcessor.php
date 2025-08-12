<?php

namespace App\Services;

use Tiptap\Editor;
use Tiptap\Extensions\StarterKit;

class TipTapContentProcessor
{
    /**
     * Convert HTML to TipTap JSON format
     */
    public static function htmlToJson(string $html): array
    {
        $editor = new Editor([
            'extensions' => [
                new StarterKit(),
            ]
        ]);

        return $editor->setContent($html)->getDocument();
    }

    /**
     * Convert TipTap JSON to HTML
     */
    public static function jsonToHtml(array $json): string
    {
        $editor = new Editor([
            'extensions' => [
                new StarterKit(),
            ]
        ]);

        return $editor->setContent($json)->getHTML();
    }

    /**
     * Clean and sanitize TipTap content
     */
    public static function sanitizeContent(string $html): string
    {
        // Strip potentially dangerous tags and attributes
        $allowedTags = [
            'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
            'p', 'br',
            'strong', 'b', 'em', 'i', 'u', 's',
            'ul', 'ol', 'li',
            'blockquote',
            'a', 'img',
            'pre', 'code',
            'table', 'thead', 'tbody', 'tr', 'th', 'td'
        ];

        $allowedAttributes = [
            'href', 'src', 'alt', 'title', 'class', 'id',
            'width', 'height', 'target', 'rel'
        ];

        return strip_tags($html, '<' . implode('><', $allowedTags) . '>');
    }

    /**
     * Extract plain text from TipTap HTML content
     */
    public static function extractPlainText(string $html): string
    {
        return strip_tags($html);
    }

    /**
     * Calculate reading time for content
     */
    public static function calculateReadingTime(string $html): int
    {
        $plainText = self::extractPlainText($html);
        $wordCount = str_word_count($plainText);
        
        // Average reading speed is 200-250 words per minute
        $wordsPerMinute = 200;
        
        return max(1, ceil($wordCount / $wordsPerMinute));
    }

    /**
     * Generate excerpt from TipTap content
     */
    public static function generateExcerpt(string $html, int $length = 160): string
    {
        $plainText = self::extractPlainText($html);
        
        if (strlen($plainText) <= $length) {
            return $plainText;
        }
        
        return substr($plainText, 0, $length) . '...';
    }

    /**
     * Extract headings for table of contents
     */
    public static function extractHeadings(string $html): array
    {
        $headings = [];
        
        if (preg_match_all('/<h([1-6])(?:[^>]*)>(.*?)<\/h[1-6]>/i', $html, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $headings[] = [
                    'level' => (int) $match[1],
                    'text' => strip_tags($match[2]),
                    'anchor' => \Illuminate\Support\Str::slug(strip_tags($match[2]))
                ];
            }
        }
        
        return $headings;
    }

    /**
     * Add anchor links to headings
     */
    public static function addHeadingAnchors(string $html): string
    {
        return preg_replace_callback(
            '/<h([1-6])(?:[^>]*)>(.*?)<\/h[1-6]>/i',
            function ($matches) {
                $level = $matches[1];
                $text = $matches[2];
                $anchor = \Illuminate\Support\Str::slug(strip_tags($text));
                
                return "<h{$level} id=\"{$anchor}\">{$text}</h{$level}>";
            },
            $html
        );
    }

    /**
     * Extract images from content
     */
    public static function extractImages(string $html): array
    {
        $images = [];
        
        if (preg_match_all('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $html, $matches)) {
            foreach ($matches[1] as $src) {
                $images[] = $src;
            }
        }
        
        return $images;
    }
}