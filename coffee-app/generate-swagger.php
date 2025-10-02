<?php

require_once 'vendor/autoload.php';

use OpenApi\Generator;

$openapi = Generator::scan(['app/Http/Controllers']);

file_put_contents('storage/api-docs/api-docs.json', $openapi->toJson());
echo "Swagger documentation generated successfully!\n";