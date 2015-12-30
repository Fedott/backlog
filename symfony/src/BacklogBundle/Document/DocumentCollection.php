<?php
namespace BacklogBundle\Document;

use Closure;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\MongoDB\CursorInterface;
use Traversable;

class DocumentCollection extends ArrayCollection
{
    /**
     * @param array|CursorInterface|Collection $elements
     */
    public function __construct($elements = array())
    {
        if ($elements instanceof CursorInterface) {
            $elements = $elements->toArray(false);
        } elseif ($elements instanceof Collection) {
            $elements = $elements->toArray();
        }
        parent::__construct($elements);
    }

    /**
     * @param string $field
     * @return array
     */
    public function extractField($field)
    {
        return array_map(
            function ($value) use ($field) {
                return $value->$field;
            },
            $this->getValues()
        );
    }

    /**
     * @return array
     */
    public function getIds()
    {
        return $this->extractField('id');
    }

    /**
     * @param callable|Closure $sortCallback
     * @return $this
     */
    public function usort(Closure $sortCallback)
    {
        $values = $this->getValues();
        usort(
            $values,
            $sortCallback
        );
        $this->setValues($values);
        return $this;
    }

    /**
     * Sort by keys
     * @return $this
     */
    public function ksort()
    {
        $values = $this->toArray();
        ksort($values);
        $this->setValues($values);
        return $this;
    }

    /**
     * @param array|Traversable $values
     * @return $this
     */
    public function setValues($values)
    {
        $this->clear();
        foreach ($values as $key => $value) {
            $this->set($key, $value);
        }
        return $this;
    }

    /**
     * @param array $elements
     */
    public function append(array $elements)
    {
        foreach ($elements as $element) {
            $this->add($element);
        }
    }

    /**
     * @return string
     */
    public static function getClassName()
    {
        return get_called_class();
    }
}