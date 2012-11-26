<?php
/*
 * Copyright 2012 Victor Berchet <victor@suumit.com>
 *
 * Licensed under the MIT License
 */

namespace GEPExterns;

class Method
{
    /**
     * @var string
     */
    private $returnType;

    /**
     * @var Variable[]
     */
    private $arguments;

    /**
     * @var string
     */
    private $name;

    /**
     * @param string     $name
     * @param string     $returnType
     * @param Variable[] $arguments
     */
    public function __construct($name, $returnType, array $arguments = array())
    {
        $this->name = preg_replace('/[^a-z0-9_.-]/i', '', $name);
        $this->returnType = preg_replace('/[^a-z0-9_.-]/i', '', $returnType);
        $this->arguments = $arguments;
    }

    /**
     * @return array|Variable[]
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getReturnType()
    {
        return $this->returnType;
    }

}