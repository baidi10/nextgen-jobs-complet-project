<?php
namespace Libs\Parsedown;

use Parsedown;

class SafeParsedown extends Parsedown {
    protected function inlineLink($excerpt) {
        $link = parent::inlineLink($excerpt);
        
        if (!isset($link)) {
            return null;
        }

        // Only allow http/https protocols
        $href = $link['element']['attributes']['href'] ?? '';
        if (!preg_match('/^https?:\/\//i', $href)) {
            return null;
        }

        // Add rel="noopener noreferrer" for security
        $link['element']['attributes']['rel'] = 'noopener noreferrer';
        
        return $link;
    }

    protected function inlineImage($excerpt) {
        $image = parent::inlineImage($excerpt);
        
        if (!isset($image)) {
            return null;
        }

        // Only allow http/https protocols for images
        $src = $image['element']['attributes']['src'] ?? '';
        if (!preg_match('/^https?:\/\//i', $src)) {
            return null;
        }

        return $image;
    }

    protected function blockFencedCode($line) {
        $code = parent::blockFencedCode($line);
        
        if (isset($code)) {
            // Escape any HTML in code blocks
            $code['element']['text'] = htmlspecialchars($code['element']['text'], ENT_QUOTES);
        }
        
        return $code;
    }

    public function text($text) {
        // Convert all text to UTF-8 first
        $text = mb_convert_encoding($text, 'UTF-8', mb_detect_encoding($text));
        
        // Parse the markdown
        $html = parent::text($text);
        
        // Additional sanitization
        $html = $this->sanitizeHtml($html);
        
        return $html;
    }

    private function sanitizeHtml($html) {
        // Basic HTML sanitization
        $html = strip_tags($html, '<p><a><strong><em><code><pre><ul><ol><li><blockquote><h1><h2><h3><h4><h5><h6><br><hr>');
        
        // Remove any onclick, onmouseover, etc. attributes
        $html = preg_replace('/ on\w+="[^"]*"/', '', $html);
        
        return $html;
    }
}