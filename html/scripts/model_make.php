<?php

require_once __DIR__ . '/autoload.php';

use eru123\helper\StringUtil;

if (!isset($argv[1]) || empty($argv[1])) {
    echo ('No model name given');
    exit(1);
}

$classname = StringUtil::camel_case_to_pascal_case($argv[1]);
$tablename = StringUtil::camel_case_to_snake_case($argv[1]);

$template = <<<TEMPLATE
<?php

namespace App\Models;

class $classname extends AbstractModel
{   
    protected static \$table = '$tablename';
    protected static \$allowed = [];
    protected static \$created_at = false;
    protected static \$updated_at = false;
    protected static \$deleted_at = false;
    protected static \$disabled_at = false;
    protected static \$primary_key = false;

    protected static \$date_format = 'Y-m-d H:i:s';
    protected static \$soft_delete = false;
    protected static \$use_created_at = false;
    protected static \$use_updated_at = false;
}
TEMPLATE;

$filename = __APP__ . '/Models/' . $classname . '.php';

if (file_exists($filename)) {
    echo 'File already exists: ', $filename, PHP_EOL;
    exit(1);
}

$w = file_put_contents($filename, $template);

if ($w === false) {
    echo 'Could not write file: ', $filename, PHP_EOL;
    exit(1);
}

echo 'File written: ', $filename, PHP_EOL;
