<?php
/*
 * Copyright 2012 Victor Berchet <victor@suumit.com>
 *
 * Licensed under the MIT License
 */

namespace GEPExterns;

class JsClass
{
    /**
     * @var Method[]
     */
    private $methods = array();

    /**
     * @var Property[]
     */
    private $properties = array();

    /**
     * @var array
     */
    private $enums = array();

    /**
     * @var JsClass[]
     */
    private $parents = array();

    /**
     * @var string
     */
    private $name;

    /**
     * @param $name string The name
     */
    public function __construct($name)
    {
        $this->name = preg_replace('/[^a-z0-9_.-]/i', '', $name);
    }

    /**
     * @return string The class name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return array|Method[]
     */
    public function getMethods()
    {
        sort($this->methods);
        return $this->methods;
    }

    /**
     * @return array|Property[]
     */
    public function getProperties()
    {
        sort($this->properties);
        return $this->properties;
    }

    /**
     * @return array
     */
    public function getParents()
    {
        return array_unique($this->parents);
    }

    /**
     * @return array
     */
    public function getEnums()
    {
        sort($this->enums);
        return $this->enums;
    }

    /**
     * @param $method Method|Method[] The method to add
     */
    public function addMethod($method)
    {
        foreach(is_array($method) ? $method : array($method) as $m) {
            $this->methods[$m->getName()] = $m;
        }
    }

    /**
 * @param $property Variable The property to add
 */
    public function addProperty(Variable $property)
    {
        if ('Enum' === substr($property->getType(), -5)) {
            if (array_key_exists($property->getType(), $this->enums)) {
                $this->enums[$property->getType()][] = $property->getName();
            } else {
                $this->enums[$property->getType()] = array($property->getName());
            }
        } else {
            $this->properties[$property->getName()] = $property;
        }
    }

    /**
     * @param $parent string|string[] The parent to add
     */
    public function addParent($parent)
    {
        foreach(is_array($parent) ? $parent : array($parent) as $p) {
            $p = preg_replace('/[^a-z0-9_.-]/i', '', $p);
            if ($p !== $this->name) {
                $this->parents[] = $p;
            }
        }
    }
}