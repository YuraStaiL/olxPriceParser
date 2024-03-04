<?php

namespace Models;

use Exception;
use PDO;

class Model
{
    protected PDO $connection;
    protected string $table;
    protected array|string $primary;

    public function __construct(
        ?Database $database = null
    ) {
        if (!isset($this->table)) {
            throw new Exception(get_class($this) . ' must have a $table');
        }

        if ($database === null) {
            $this->connection = Database::getInstance()->connection;
        } else {
            $this->connection = $database->connection;
        }
    }

    protected static function make(
        array       $params,
        ?Database   $database = null
    ): static {
        $model = new static($database);
        foreach ($params as $column => $value) {
            $model->{$column} = $value;
        }

        return $model;
    }

    private function getPrimaryCondition(): string
    {
        if (is_array($this->primary)) {
            $totalKeysCnt = count($this->primary);
            $current = 0;
            $conditions = '';
            foreach ($this->primary as $primaryKey) {
                $current++;
                $conditions .= sprintf(
                    "%s = :%s",
                    $primaryKey,
                    $primaryKey
                );

                if ($current < $totalKeysCnt) {
                    $conditions .= ' AND ';
                }
            }
        } else {
            $conditions = "$this->primary = :$this->primary";
        }

        return $conditions;
    }

    private function getPrimaryBindins(...$ids): array
    {
//        echo "<br>" . print_r($ids, true);
        if (is_array($this->primary)) {
            foreach ($this->primary as $key => $primaryKey) {
                $binds[$primaryKey] = $ids[$key];
            }
        } else {
            $binds[$this->primary] = $ids[0];
        }

        return $binds;
    }

    public function getRecord(
        ...$ids
    ): ?self {
        $conditions = $this->getPrimaryCondition();
        $binds = $this->getPrimaryBindins(...$ids);
        $sql = "SELECT * FROM $this->table WHERE $conditions";
        $sth = $this->connection->prepare($sql);
        $result = $sth->execute($binds);
        if ($result) {
            $record = $sth->fetch(PDO::FETCH_ASSOC);
        }

        if (isset($record) && $record) {
            return static::make($record);
        }

        return null;
//        $record = mysqli_fetch_array($result, MYSQLI_ASSOC);
        //Properties...foreach($record as $key=>$value) {$this->$key = $value;
    }

    public static function create(
        $assocValues
    ): ?self {
        $pdo = Database::getInstance();
        $toBind = implode(
            ',',
            array_map(
                function ($column) {
                    return ":$column";
                },
                array_keys($assocValues)
            )
        );
        $insertInto = implode(
            ',',
            array_keys($assocValues)
        );
        $table = (new static())->table;
        $sql = "INSERT INTO {$table} ($insertInto)
          VALUES ($toBind)";

        $sth = $pdo->connection->prepare($sql);
        $result = $sth->execute($assocValues);

        if ($result) {
            return self::make($assocValues, $pdo);
        }

        return null;
    }

    /**
     * @return array
     */
    public function all(): array
    {
        $sql = "SELECT *
                FROM $this->table
                ";
        $sth = $this
            ->connection
            ->prepare($sql);
        $result = $sth->execute();
        $keyBy = [];
        if ($result) {
            $sth->setFetchMode(PDO::FETCH_CLASS, static::class);
            $records = $sth->fetchAll();
            foreach ($records as $record) {
                $keyBy[$record->olx_product] = $record;
            }
        }
//        PEAR::mail

        return $keyBy;
    }

    public function update(array $params)
    {
        $primaryCondition = $this->getPrimaryCondition();
        $primaryIds = [];
        if (is_array($this->primary)) {
            foreach ($this->primary as $primaryKey) {
                $primaryIds[] = $this->{$primaryKey};
            }
        } else {
            $primaryIds[] = $this->{$this->primary};
        }
        $bindings = $this->getPrimaryBindins(...$primaryIds);
        $set = '';
        echo "\n" . print_r($bindings, true) . "\n";

        $totalCnt = count($params);
        $current = 0;
        foreach ($params as $column => $value) {
            $this->{$column} = $value;
            $bindings["{$column}ToUpdate"] = $value;
            $current++;
            $set .= "$column = :{$column}ToUpdate";
            if ($current < $totalCnt) {
                $set .= ', ';
            }
        }

        $sql = "UPDATE $this->table
                SET $set
                WHERE $primaryCondition;";

        $sth = $this
            ->connection
            ->prepare($sql);
        $result = $sth->execute($bindings);
    }

    public static function updateWhere(
        array $where,
        array $updateData
    ): bool {
        $whereCondition = '';
        $totalWhereConditionCnt = count($where);
        $currentWhereCondition = 1;

        foreach ($where as $column => $value) {
            $whereCondition .= "$column = :$column";
            $bindings[$column] = $value;

            if ($currentWhereCondition < $totalWhereConditionCnt) {
                $whereCondition .= " AND ";
            }
            $currentWhereCondition++;
        }

        $set = '';
        $totalUpdateCnt = count($updateData);
        $currentUpdate = 0;
        foreach ($updateData as $column => $value) {
            $bindings["{$column}ToUpdate"] = $value;
            $currentUpdate++;
            $set .= "$column = :{$column}ToUpdate";
            if ($currentUpdate < $totalUpdateCnt) {
                $set .= ', ';
            }
        }
        $tableName = (new static())->table;
        $sql = "UPDATE $tableName
                SET $set
                WHERE $whereCondition;";
        $sth = Database::getInstance()
            ->connection
            ->prepare($sql);

        return $sth->execute($bindings ?? []);
    }
}