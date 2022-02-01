<?php

namespace Cerpus\Helper\Traits;


use Illuminate\Support\Collection;

/**
 * Trait CreateTrait
 *
 * @package Cerpus\Helper\Traits
 */
trait CreateTrait
{

    public $wasRecentlyCreated = false;
    private $isDirty = false;

    public $metaProperties = ['wasRecentlyCreated', 'isDirty'];

    /**
     * @param  mixed  $attributes
     *
     * @return CreateTrait
     * @throws \OutOfRangeException
     *
     */
    public static function create($attributes = null)
    {
        $self = new self();
        if (is_null($attributes)) {
            return $self;
        }
        $properties = get_object_vars($self);

        if (! is_array($attributes)) {
            $arguments = func_get_args();
            $attributes = [];

            // Only update properties defined in the class directly, not properties inherited or from traits
            $updatableProperties = $self->removeParentClassProperties($properties);
            $updatableProperties = $self->removeTraitProperties($updatableProperties);

            if (count($updatableProperties) < count($arguments)) {
                throw new \OutOfRangeException('More arguments than updatable properties.');
            }

            $updatablePropertiesNumIndex = array_keys($updatableProperties);

            foreach ($arguments as $index => $value) {
                $property = $updatablePropertiesNumIndex[$index];
                $attributes[$property] = $value;
            }
        }
        foreach ($attributes as $attribute => $value) {
            if (! $self->isGuarded($attribute) && array_key_exists($attribute, $properties)) {
                $self->isDirty = $self->isDirty || $self->$attribute !== $value;
                if ($attribute !== 'isDirty') {
                    $self->$attribute = $value;
                }
            }
        }

        return $self;
    }

    private function removeParentClassProperties(array $properties) :array
    {
        if ($parentClass = get_parent_class($this)) {
            if ($parentClassAttributes = get_class_vars($parentClass)) {
                foreach ($parentClassAttributes as $key => $value) {
                    unset($properties[$key]);
                }
            }
        }

        return $properties;
    }

    private function removeTraitProperties(array $properties) :array
    {
        if ($traits = (new \ReflectionClass($this))->getTraitNames()) {
            foreach ($traits as $trait) {
                $traitProps = (new \ReflectionClass($trait))->getProperties(\ReflectionProperty::IS_STATIC
                    | \ReflectionProperty::IS_PROTECTED
                    | \ReflectionProperty::IS_PRIVATE
                    | \ReflectionProperty::IS_PUBLIC);
                foreach ($traitProps as $traitProperty) {
                    unset($properties[$traitProperty->name]);
                }
            }
        }


        return $properties;
    }

    /**
     * @param  string  $attribute
     *
     * @return bool
     */
    private function isGuarded($attribute)
    {
        if (strtolower($attribute) === 'guarded') {
            return true;
        }

        return ! empty($this->guarded) && in_array($attribute, $this->guarded);
    }

    /**
     * @param  mixed  $includeMetaProperties
     *
     * @return array
     */
    public function toArray($includeMetaProperties = false)
    {
        $returnArray = [];
        $properties = get_object_vars($this);
        $metaProperties = $includeMetaProperties === true ? [] : $this->metaProperties;
        if (! is_null($includeMetaProperties) && ! is_bool($includeMetaProperties)) {
            if (is_string($includeMetaProperties)) {
                $metaProperties = array_diff($this->metaProperties, explode(",", $includeMetaProperties));
            } else {
                if (is_array($includeMetaProperties)) {
                    $metaProperties = $includeMetaProperties;
                }
            }
        }
        foreach ($metaProperties as $index => $field) {
            unset($properties[$field]);
        }
        unset($properties['metaProperties']);
        foreach ($properties as $property => $value) {
            if ($this->$property instanceof Collection) {
                $returnArray[$property] = $this->$property->map(function ($element) use ($includeMetaProperties) {
                    if (is_callable([$element, "toArray"])) {
                        return $element->toArray($includeMetaProperties);
                    } else {
                        return $element;
                    }
                })->toArray();
            } else {
                $returnArray[$property] = $value;
            }
        }

        return $returnArray;
    }

    /**
     * @return bool
     */
    public function isDirty()
    {
        if ($this->isDirty === true) {
            return true;
        }

        $self = new self();

        return $self->toJson() !== $this->toJson();
    }

    /**
     * @return string
     */
    public function toJson()
    {
        return json_encode($this);
    }

    public function setIsDirty($isDirty)
    {
        $this->isDirty = $isDirty;
    }
}
