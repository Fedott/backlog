<?php
namespace BacklogBundle\Serializer;

use Doctrine\Common\Collections\Collection;
use Doctrine\MongoDB\CursorInterface;
use Doctrine\MongoDB\Iterator;
use JMS\Serializer\Context;
use JMS\Serializer\EventDispatcher\PreSerializeEvent;
use JMS\Serializer\JsonSerializationVisitor;
use Metadata\MetadataFactoryInterface;
use ArrayObject;

/**
 */
class CollectionHandler
{
    /**
     * @var MetadataFactoryInterface
     */
    protected $metadataFactory;

    /**
     * @param MetadataFactoryInterface $metadataFactory
     */
    public function __construct(MetadataFactoryInterface $metadataFactory)
    {
        $this->metadataFactory = $metadataFactory;
    }

    /**
     * @param JsonSerializationVisitor $visitor
     * @param Collection|Iterator $collection
     * @param array $type
     * @param Context $context
     * @return array|\ArrayObject|mixed
     */
    public function serializeCollectionToJson(
        JsonSerializationVisitor $visitor,
        $collection,
        array $type,
        Context $context
    ) {
        $type['name'] = 'array';
        $preRoot = $visitor->getRoot();

        $result = $visitor->visitArray(array_values($collection->toArray()), $type, $context);

        if ($result instanceof ArrayObject) {
            $result = $result->getArrayCopy();
        }

        // FIXME Dirty hack to avoid empty embedded document modify root to ArrayObject
        $postRoot = $visitor->getRoot();
        if (null === $preRoot && $postRoot instanceof ArrayObject) {
            $visitor->setRoot($postRoot->getArrayCopy());
        }

        return $result;
    }

    /**
     * @param PreSerializeEvent $event
     */
    public function onSerializerPreSerialize(PreSerializeEvent $event)
    {
        if ($event->getObject() instanceof Collection || $event->getObject() instanceof CursorInterface) {
            $event->setType('Collection');
        }
    }
}
