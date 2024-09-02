<?php

namespace JackedPhp\LiteConnect\Query;

class QueryBuilder
{
    private string $table;

    /**
     * @var array<string>
     */
    private array $where = [];

    /**
     * @var array<mixed>
     */
    private array $params = [];

    public function table(string $table): self
    {
        $this->table = $table;
        return $this;
    }

    public function where(string $column, string $operator, mixed $value): self
    {
        $this->where[] = "$column $operator ?";
        $this->params[] = $value;
        return $this;
    }

    public function build(): array
    {
        $sql = "SELECT * FROM {$this->table}";
        if (!empty($this->where)) {
            $sql .= " WHERE " . implode(' AND ', $this->where);
        }
        return [$sql, $this->params];
    }
}