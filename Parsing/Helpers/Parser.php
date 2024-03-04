<?php

namespace Parsing\Helpers;

use Exceptions\EmptyHtmlException;
use Parsing\OlxPage;

class Parser
{
    private ?string $html = null;

    /**
     * @param string $link
     */
    public function __construct(private readonly string $link)
    {
        libxml_use_internal_errors(true);
    }

    /**
     * @return OlxPage
     * @throws EmptyHtmlException
     */
    public function parse(): OlxPage
    {
        $html = file_get_contents($this->link);

        if (!$html) {
            throw new EmptyHtmlException();
        }

        return new OlxPage($html);
    }
}