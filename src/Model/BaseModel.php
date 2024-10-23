<?php

namespace JackedPhp\LiteConnect\Model;

use InvalidArgumentException;
use JackedPhp\LiteConnect\Connection\Connection;
use PDO;

abstract class BaseModel
{
    protected string $table;

    protected ?string $primaryKey = null;

    /** @var array<string> */
    protected array $attributes = [];

    /** @var array<array{column: string, condition: string, value: string}> */
    protected array $where = [];

    protected array $orderBy = [];

    public function __construct(
        protected Connection $connection,
        ?array $data = null,
    ) {
        if (null === $data) {
            return;
        }

        foreach ($data as $key => $value) {
            $this->attributes[$key] = $value;
        }
    }

    public function __get(string $name): mixed
    {
        return $this->attributes[$name] ?? null;
    }

    public function reset(): void
    {
        $this->attributes = [];
        $this->where = [];
    }

    /**
     * @param int $id
     */
    public function find(int $id): static
    {
        $stmt = $this->connection->getPDO()->prepare("SELECT * FROM {$this->table} WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        foreach ($result as $key => $value) {
            $this->attributes[$key] = $value;
        }

        return $this;
    }

    /**
     * @param array<array-key, mixed> $data
     * @return static
     */
    public function create(array $data): static
    {
        $pdo = $this->connection->getPDO();

        $columns = implode(', ', array_keys($data));
        $values = implode(', ', array_fill(0, count($data), '?'));
        $stmt = $pdo->prepare("INSERT INTO {$this->table} ($columns) VALUES ($values)");
        $stmt->execute(array_values($data));

        $stmt = $pdo->prepare("SELECT * FROM {$this->table} WHERE id = :id");
        $stmt->execute(['id' => $pdo->lastInsertId()]);

        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        foreach ($data as $key => $value) {
            $this->attributes[$key] = $value;
        }

        return $this;
    }

    public function where(string $column, string $condition = '=', mixed $value = null): static
    {
        $this->where[] = [
            'column' => $column,
            'condition' => $condition,
            'value' => $value,
        ];

        return $this;
    }

    public function orderBy(string $column, string $direction = 'ASC'): static
    {
        $this->orderBy[] = [
            'column' => $column,
            'direction' => $direction,
        ];

        return $this;
    }

    /**
     * @return array<array<static>>
     */
    public function get(): array
    {
        $pdo = $this->connection->getPDO();

        $stmt = $pdo->prepare($this->getSql());
        $stmt->execute(array_map(fn($condition) => $condition['value'], $this->where));
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn($item) => new static($this->connection, $item), $data);
    }

    public function getSql(): string
    {
        return "SELECT * FROM {$this->table} {$this->getWhereStatement()} {$this->getOrderByStatement()}";
    }

    protected function getWhereStatement(): string
    {
        $where = '';
        if (!empty($this->where)) {
            $where = 'WHERE ';
            foreach ($this->where as $condition) {
                $where .= "{$condition['column']} {$condition['condition']} ? AND ";
            }
            $where = rtrim($where, ' AND ');
        }

        return $where;
    }

    protected function getOrderByStatement(): string
    {
        $orderBy = '';
        if (!empty($this->orderBy)) {
            $orderBy = 'ORDER BY ';
            foreach ($this->orderBy as $order) {
                $orderBy .= "{$order['column']} {$order['direction']}, ";
            }
            $orderBy = rtrim($orderBy, ', ');
        } elseif ($this->primaryKey) {
            $orderBy = "ORDER BY {$this->primaryKey} ASC";
        }

        return $orderBy;
    }

    public function delete(): void
    {
        $pdo = $this->connection->getPDO();

        if ($this->id) {
            $this->where('id', '=', $this->id);
        }

        $sqlStatement = "DELETE FROM {$this->table} {$this->getWhereStatement()}";
        $stmt = $pdo->prepare($sqlStatement);
        $stmt->execute(array_map(fn($condition) => $condition['value'], $this->where));
        $this->reset();
    }

    public function update(array $data): bool
    {
        if (empty($data) || $this->id === null) {
            throw new InvalidArgumentException('Invalid data or ID not set.');
        }

        $pdo = $this->connection->getPDO();
        $columns = implode(', ', array_map(fn($column) => "$column = ?", array_keys($data)));
        $stmt = $pdo->prepare("UPDATE {$this->table} SET $columns WHERE id = :id");

        return $stmt->execute(array_merge(array_values($data), ['id' => $this->id]));
    }
}