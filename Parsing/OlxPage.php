<?php

namespace Parsing;

use DOMDocument;
use DOMXPath;
use Exceptions\EmptyHtmlException;

class OlxPage
{
    public function __construct(private string $html)
    {
    }

    /**
     * @return ?int
     */
    public function getProductId(): ?int
    {
        $dom = new DOMDocument();
        $dom->loadHTML($this->html);

        $xpath = new DOMXPath($dom);
        $classname = 'css-12hdxwj er34gjf0';
        $id = $xpath->query("//*[contains(@class, '$classname')]");

        foreach ($id as $i) {
            return (int) preg_replace('/[^0-9]/', '', $i->textContent);
        }

        return null;
    }

    /**
     * @return int|null
     */
    public function getPrice(): ?int
    {
        $dom = new DOMDocument();
        $dom->loadHTML($this->html);

        $xpath = new DOMXPath($dom);
        $priceContainer = $xpath->query('//*[@data-testid="ad-price-container"]')->item(0);
        if ($priceContainer) {
            $priceElement = $priceContainer->getElementsByTagName('h3')->item(0);
            if ($priceElement) {
                $price = $priceElement->textContent;
                return (int) preg_replace('/[^0-9]/', '', $price);
            }
        }

        return null;
    }

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        $dom = new DOMDocument();
        $dom->loadHTML($this->html);

        $xpath = new DOMXPath($dom);
        $titleContainer = $xpath->query('//*[@data-cy="ad_title"]')->item(0);
        if ($titleContainer) {
            $priceElement = $titleContainer->getElementsByTagName('h4')->item(0);

            if ($priceElement) {
                return (string) $priceElement->textContent;
            }
        }

        return null;
    }
}