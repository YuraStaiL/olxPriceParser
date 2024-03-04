<?php

namespace Models;

use PDO;

class Subscription extends Model
{
    public int $product;
    public string $email;

    protected string $table = 'subscriptions';
    protected string|array $primary = [
        'product',
        'email'
    ];

    protected array $fields = [
        'product',
        'email'
    ];

    public function groupByProduct(): array
    {
        $sql = "SELECT *
                FROM $this->table
                ";
        $sth = $this
            ->connection
            ->prepare($sql);
        $result = $sth->execute();
        $groupBy = [];
        if ($result) {
            $sth->setFetchMode(PDO::FETCH_CLASS, Subscription::class);
            $records = $sth->fetchAll();
            foreach ($records as $record) {
                $groupBy[$record->product][] = $record;
            }
        }

        return $groupBy;
    }

    public function existsNotConfirmed()
    {

    }
}