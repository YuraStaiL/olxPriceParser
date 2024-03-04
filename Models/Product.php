<?php

namespace Models;

class Product extends Model
{
    public int $olx_product;
    public int $price;
    public string $link;
    public string $title;

    protected string $table = 'products';
    protected string|array $primary = 'olx_product';

    protected array $fields = [
        'olx_product',
        'price',
        'link',
        'title'
    ];
}