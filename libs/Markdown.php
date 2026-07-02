<?php
// libs/Markdown.php
require_once __DIR__ . '/Parsedown/Parsedown.php';

class SafeParsedown extends Parsedown {
    protected function blockFencedCodeComplete($Block) {
        return $Block;
    }

    protected function inlineLink($Excerpt) {
        $link = parent::inlineLink($Excerpt);
        
        if (!isset($link)) {
            return null;
        }
        
        // Add nofollow and security attributes
        $link['element']['attributes']['rel'] = 'nofollow noopener noreferrer';
        $link['element']['attributes']['target'] = '_blank';
        
        return $link;
    }
}

class Markdown {
    private $parser;
    
    public function __construct() {
        $this->parser = new SafeParsedown();
        $this->parser->setSafeMode(true);
        $this->parser->setMarkupEscaped(true);
    }

    public function parse($text) {
        $html = $this->parser->text($text);
        return $this->sanitize($html);
    }

    private function sanitize($html) {
        $allowed = [
            'a' => ['href', 'title', 'rel', 'target'],
            'br' => [],
            'code' => [],
            'pre' => [],
            'strong' => [],
            'em' => [],
            'ul' => [],
            'ol' => [],
            'li' => [],
            'p' => [],
            'h1' => [], 'h2' => [], 'h3' => [], 'h4' => [], 'h5' => [], 'h6' => [],
        ];
        
        return strip_tags($html, array_keys($allowed));
    }
}