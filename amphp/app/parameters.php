<?php
declare(strict_types = 1);

use function DI\env;

return [
    'redis.uri' => env('REDIS_URI', 'tcp://localhost:6379?database=11'),
];
