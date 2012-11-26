<?php
    /*
     * Copyright 2012 Victor Berchet <victor@suumit.com>
     *
     * Licensed under the MIT License
     */

namespace GEPExterns;

class Variable
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $type;

    /**
     * @param string $name
     * @param string $type
     */
    public function __construct($name, $type)
    {
        $this->name = preg_replace('/[^a-z0-9_.-]/i', '', $name);
        $this->type = preg_replace('/[^a-z0-9_.-]/i', '', $type);
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
    public function getType()
    {
        return $this->type;
    }
}