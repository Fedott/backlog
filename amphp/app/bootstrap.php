<?php

require_once __DIR__ . "/../vendor/autoload.php";

$containerBuilder = new \DI\ContainerBuilder();
$containerBuilder->addDefinitions(__DIR__ . "/services.php");
$containerBuilder->addDefinitions(__DIR__ . "/parameters.php");

$container = $containerBuilder->build();
