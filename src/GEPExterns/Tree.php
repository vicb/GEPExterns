<?php
    /*
     * Copyright 2012 Victor Berchet <victor@suumit.com>
     *
     * Licensed under the MIT License
     */

namespace GEPExterns;

class Tree
{
    /**
     * @var JsClass[]
     */
    private $jsClasses;

    /**
     * @var array
     */
    private $parents;

    /**
     * @param JsClass[] $jsClasses
     */
    public function __construct(array $jsClasses)
    {
        $this->jsClasses = $jsClasses;
        $this->computeHierarchy();
        $this->fixAbstractParents();
    }

    /**
     * @return JsClass[]
     */
    public function getClasses()
    {
        return $this->jsClasses;
    }

    /**
     * @return array
     */
    public function getParents()
    {
        return $this->parents;
    }

    /**
     * Fix for abstract classes
     */
    private function fixAbstractParents()
    {
        // class => parent
        $parents = array(
            'KmlMultiGeometry'    => 'KmlGeometry',
            'KmlAltitudeGeometry' => 'KmlGeometry',
            'KmlStyle'            => 'KmlStyleSelector',
            'KmlStyleMap'         => 'KmlStyleSelector',
        );

        foreach ($parents as $class => $parent) {
            $this->parents[$class] = $parent;
        }
    }

    private function computeHierarchy()
    {
        $nbParents = array();

        // Compute the number of parents for each class
        foreach ($this->jsClasses as $jsClass) {
            foreach ($jsClass->getParents() as $parentClass) {
                if (array_key_exists($parentClass, $this->jsClasses)) {
                    $jsClass->addParent($this->jsClasses[$parentClass]->getParents());
                }
            }
            $nbParents[$jsClass->getName()] = count($jsClass->getParents());
        }

        foreach ($this->jsClasses as $jsClass) {
            $className = $jsClass->getName();
            $nbParentClasses = array_key_exists($className, $nbParents) ? $nbParents[$className] : 0 ;
            if (0 < $nbParentClasses) {
                foreach ($jsClass->getParents() as $parentClassName) {
                    if ($nbParents[$parentClassName] === $nbParentClasses - 1) {
                        $this->parents[$className] = $parentClassName;
                        break;
                    }
                }
            }
        }
    }


}