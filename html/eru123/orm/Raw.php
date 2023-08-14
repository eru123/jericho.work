<?php

namespace eru123\orm;

/**
 * Pre-compiled SQL Query. Supports placeholders bindings and raw values.
 */
class Raw
{
    protected $query = null;

    public function __construct(protected string $sql, protected array $params = [])
    {
        $this->query = $sql;

        if (!empty($params)) {
            $tmp_params = [];
            if (static::is_array($params)) {
                $idxl = count($params);
                foreach ($params as $index => $param) {
                    $idxkey = str_pad($index, strlen($idxl), '0', STR_PAD_LEFT);
                    $param_key = ':p__' . $idxkey;
                    $this->query = preg_replace('/\?/', $param_key, $this->query, 1);
                    $tmp_params[$param_key] = $param;
                }
                $params = &$tmp_params;
            }

            foreach ($params as $key => $param) {
                $key = preg_replace('/^\:/', '', $key);
                if ($param instanceof static) {
                    $value = $param->__toString();
                } elseif (is_int($param) || is_float($param)) {
                    $value = $param;
                } elseif (is_null($param)) {
                    $value = 'NULL';
                } elseif (is_bool($param)) {
                    $value = $param ? 1 : 0;
                } else if (is_array($param)) {
                    $value = static::in($param);
                } else {
                    $value = "'" . addslashes($param) . "'";
                }

                $this->query = str_replace(":$key", $value, $this->query);
            }
        }
    }

    private static function is_array($value)
    {
        return array_keys($value) === range(0, count($value) - 1);
    }

    public function __toString(): string
    {
        return $this->query ?? $this->sql;
    }
    public function __invoke(): string
    {
        return $this->__toString();
    }
    public static function build(...$args): static
    {
        return new static(...$args);
    }

    public static function columns(string|array $names, $wrapper = '`'): static
    {
        if (empty($names)) return new static('');
        if (!is_array($names)) {
            $names = [$names];
        }

        if (static::is_array($names)) {
            $sql = $wrapper . implode("{$wrapper}, {$wrapper}", array_values($names)) . $wrapper;
            return new static($sql);
        } else {
            $selects = [];
            foreach ($names as $alias => $name) {
                $name = preg_replace('/\./', "{$wrapper}.{$wrapper}", $name);
                if (is_numeric($alias)) {
                    $selects[] = "{$wrapper}{$name}{$wrapper}";
                } else {
                    $name = preg_match('/\(/', $name) ? new static($name) : "{$wrapper}{$name}{$wrapper}";
                    $selects[] = "{$name} AS {$wrapper}{$alias}{$wrapper}";
                }
            }

            $sql = implode(', ', $selects);
            return new static($sql);
        }
    }

    public static function in(array $values): static
    {
        $sql = implode(', ', array_map(function ($value) {
            if ($value instanceof static) {
                return $value->__toString();
            } elseif (is_numeric($value)) {
                return $value;
            } elseif (is_null($value)) {
                return 'NULL';
            } elseif (is_bool($value)) {
                return $value ? 1 : 0;
            } else {
                return "'" . addslashes($value) . "'";
            }
        }, $values));

        return new static("({$sql})");
    }

    public static function table(string $name, string $alias = null): static
    {
        $sql = "`{$name}`";
        if ($alias) {
            $sql .= " AS `{$alias}`";
        }
        return new static($sql);
    }
}
