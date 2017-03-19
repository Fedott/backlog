<?php declare(strict_types=1);

namespace Fedot\Backlog;

use Symfony\Component\Inflector\Inflector;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class ExcludePluralPropertiesObjectNormalizer extends ObjectNormalizer
{
    protected function isAllowedAttribute($classOrObject, $attribute, $format = null, array $context = [])
    {
        if (Inflector::singularize($attribute) !== $attribute) {
            return false;
        }

        return parent::isAllowedAttribute(
            $classOrObject,
            $attribute,
            $format,
            $context
        );
    }
}
