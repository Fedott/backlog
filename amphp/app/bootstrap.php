<?php
declare(strict_types = 1);

require_once __DIR__ . "/../vendor/autoload.php";

\Doctrine\Common\Annotations\AnnotationRegistry::registerLoader(function($class) {
    return class_exists($class);
});

$container = require_once __DIR__ . "/container.php";
