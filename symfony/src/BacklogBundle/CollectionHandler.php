<?php
namespace BacklogBundle;

use Doctrine\Common\Collections\Collection;
use Doctrine\MongoDB\Iterator;
use Doctrine\ODM\MongoDB\Cursor;
use JMS\Serializer\Context;
use JMS\Serializer\EventDispatcher\PreSerializeEvent;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\JsonSerializationVisitor;
use JMS\Serializer\Metadata\ClassMetadata;
use JMS\Serializer\XmlSerializationVisitor;
use Metadata\MetadataFactoryInterface;
use ArrayObject;

/**
 * @DI\Service("lighthouse.core.serializer.handler.collection")
 * @DI\Tag("jms_serializer.handler", attributes={
 *      "type": "Collection",
 *      "format": "json",
 *      "direction": "serialization"
 * })
 * @DI\Tag("jms_serializer.handler", attributes={
 *      "type": "Collection",
 *      "format": "xml",
 *      "direction": "serialization"
 * })
 * @DI\Tag("jms_serializer.event_listener", attributes={
 *      "event": "serializer.pre_serialize"
 * })
 */
class CollectionHandler
{
    /**
     * @var MetadataFactoryInterface
     */
    protected $metadataFactory;

    /**
     * @DI\InjectParams({
     *      "metadataFactory" = @DI\Inject("jms_serializer.metadata_factory")
     * })
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
        if ($event->getObject() instanceof Collection || $event->getObject() instanceof Cursor) {
            $event->setType('Collection');
        }
    }
}
