<?php declare(strict_types=1);

namespace Tests\Fedot\Backlog;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Instantiator\Instantiator;
use Fedot\DataMapper\Memory\ModelManager;
use Fedot\DataMapper\Metadata\Driver\AnnotationDriver;
use Fedot\DataMapper\ModelManagerInterface;
use Metadata\MetadataFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyAccess\PropertyAccessor;

abstract class BaseTestCase extends TestCase
{
    /**
     * @var ModelManagerInterface
     */
    protected $modelManager;

    protected function setUp()
    {
        parent::setUp();

        $this->modelManager = new ModelManager(
            new MetadataFactory(new AnnotationDriver(new AnnotationReader())),
            new PropertyAccessor(),
            new Instantiator(),
            2
        );
    }
}
