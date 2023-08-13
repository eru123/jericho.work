<?php

namespace App\Models;

use PDOStatement;

interface Model
{
    /**
     * Sanitize data to be inserted or updated
     * @param array $data
     * @return array
     */
    public static function sanitize(array $data): array;
    /**
     * Insert data into the database
     * @param array $data
     * @return PDOStatement
     */
    public static function insert(array $data): PDOStatement;
    /**
     * Insert many data into the database
     * @param array $data
     * @return PDOStatement
     */
    public static function insert_many(array $data): PDOStatement;
    /**
     * Update data in the database
     * @param int|string $id
     * @param array $data
     */
    public static function update(int|string $id, array $data): PDOStatement;
    /**
     * Soft delete data in the database
     * @param int|string $id
     * @return PDOStatement
     */
    public static function delete(int|string $id): PDOStatement;
    /**
     * Hard delete data in the database
     * @param int|string $id
     * @return PDOStatement
     */
    public static function deleteUnsafe(int|string $id): PDOStatement;
    /**
     * Purge soft deleted data in the database
     * @param int|string $id
     * @return PDOStatement
     */
    public static function purge(int|string $id = null): PDOStatement;
    /**
     * Select one row from the database
     * @param int|string $id
     * @return array|null
     */
    public static function find(int|string $id): array|null|false;
    /**
     * Select many rows from the database
     * @param int|string $id
     * @return array
     */
    public static function find_many(int|string $id): array;
}
