<?php

namespace App\Plugin;

class FakeStmt
{
    private $data;
    private $rows = [];
    private $columns = [];
    public function __construct(array $data)
    {
        $this->data = $data;
        foreach ($data['rows'] as $row) {
            $this->rows[] = $row;
        }

        foreach ($data['rows'] as $row) {
            foreach ($row as $column) {
                $this->columns[] = $column;
            }
            break;
        }
    }

    public function fetchAll(): array
    {
        return $this->data['rows'];
    }

    public function fetch(): array|false
    {
        return array_shift($this->rows);
    }

    public function rowCount(): int
    {
        return $this->data['row_count'];
    }

    public function columnCount(): int
    {
        if (count($this->data['rows'])) {
            return count($this->data['rows'][0]);
        }
    }

    public function fetchColumn()
    {
        return array_shift($this->columns);
    }
}
