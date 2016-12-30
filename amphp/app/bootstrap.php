<?php
declare(strict_types = 1);

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

require_once __DIR__ . "/../vendor/autoload.php";

$container = require_once __DIR__ . "/container.php";
