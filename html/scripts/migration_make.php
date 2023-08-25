<?php

require_once __DIR__ . '/autoload.php';

use eru123\helper\StringUtil;

if (!isset($argv[1]) || empty($argv[1])) {
    echo ('No migration name given');
    exit(1);
}

$identifier = date('YmdHis') . '_' . StringUtil::camel_case_to_snake_case($argv[1]) . '.sql';
$filename = __MIGRATIONS__ . DIRECTORY_SEPARATOR . $identifier;

if (file_exists($filename)) {
    echo 'Migration already exists', PHP_EOL;
    exit(1);
}

file_put_contents($filename, '');
if (file_exists($filename)) {
    echo 'Migration created: ', $identifier, PHP_EOL;
    echo 'Full path: ', $filename, PHP_EOL;
    exit(0);
} else {
    echo ('Failed to create migration');
    exit(1);
}
