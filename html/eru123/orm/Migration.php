<?php

namespace eru123\orm;

class Migration
{
    private $data = [];
    static $dir = 'migrations';

    public function __construct(protected readonly string $type)
    {
        $data['type'] = $type;
    }

    public static function table(string $table): Migration
    {
        $migration = new Migration('table');
        $migration->data['table'] = $table;
        return $migration;
    }

    public function sql(): string
    {
        $sql = '';
        return $sql;
    }
}
