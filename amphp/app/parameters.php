<?php
declare(strict_types = 1);

$container->setParameter(
    'backlog.data-storage.redis.client.uri',
    getenv('REDIS_URI') ?: 'tcp://localhost:6379?database=11'
);
