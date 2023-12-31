<?php

namespace General\Adapters;

use PDO;
use PDOException;
use PDOStatement;

class PDOAdapter
{
    private static ?PDOAdapter $repository = null;

    private static PDO $pdo;

    public function __construct(PDO $pdo = null)
    {
        if (!is_null($pdo)) {
            self::$pdo = $pdo;
        } else {
            $dsn = 'mysql:host=' . $_ENV['DB_HOST']
                . ';dbname=' . $_ENV['DB_DB']
                . ';port=' . $_ENV['DB_PORT'];
            try {
                self::$pdo = new PDO(
                    $dsn,
                    $_ENV['DB_USER'],
                    $_ENV['DB_PASS'],
                    [PDO::ATTR_PERSISTENT => true]
                );
            } catch (PDOException $e) {
                exit("Error!: " . $e->getMessage() . "<br/>");
            }
        }
    }

    /**
     * @return PDO
     */
    public static function getPdo(): PDO
    {
        if (self::$repository === null) {
            self::$repository = new self();
        }
        return self::$pdo;
    }

    /**
     * @return PDOAdapter
     */
    public static function reconnect(): PDOAdapter
    {
        self::$repository = new self();
        return self::$repository;
    }

    public static function select(string $prepareQuery, array $params = [], int $mode = PDO::FETCH_ASSOC): array
    {
        $query = self::getPdo()->prepare($prepareQuery);
        foreach ($params as $paramName => &$paramValue) {
            $query->bindParam(':' . $paramName, $paramValue);
        }
        $query->execute();
        $result = [];
        while ($row = $query->fetch($mode)) {
            $result[] = $row;
        }
        return $result;
    }


    public static function selectOneRow(string $prepareQuery, array $params = [], int $mode = PDO::FETCH_ASSOC): ?array
    {
        $query = self::getPdo()->prepare($prepareQuery);
        foreach ($params as $paramName => &$paramValue) {
            $query->bindParam(':' . $paramName, $paramValue);
        }
        $query->execute();
        $row = $query->fetch($mode);
        return $row ?: null;
    }

    public static function insert(string $prepareQuery, array $params = []): bool
    {
        $query = self::getPdo()->prepare($prepareQuery);
        return $query->execute($params);
    }

    public static function update(string $prepareQuery, array $params = []): bool
    {
        $query = self::getPdo()->prepare($prepareQuery);
        return $query->execute($params);
    }

    public static function delete(string $prepareQuery, array $params = []): bool
    {
        $query = self::getPdo()->prepare($prepareQuery);
        return $query->execute($params);
    }

    public static function lastInsertId(string $name = null): false|string
    {
        return self::getPdo()->lastInsertId($name);
    }
}
