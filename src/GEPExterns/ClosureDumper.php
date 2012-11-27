<?php
/*
 * Copyright 2012 Victor Berchet <victor@suumit.com>
 *
 * Licensed under the MIT License
 */

namespace GEPExterns;

class ClosureDumper
{
    /**
     * @var Tree
     */
    private $tree;

    public function __construct(Tree $tree)
    {
        $this->tree = $tree;
    }

    public function dump($cacheDir)
    {
        $loader = new \Twig_Loader_Filesystem(__DIR__.'/templates');
        $twig = new \Twig_Environment($loader, array(
            'cache'       => $cacheDir,
            'auto_reload' => true
        ));
        echo $twig->render('externs.twig', array(
            'classes' => $this->tree->getClasses(),
            'parents' => $this->tree->getParents(),
            'typeMap' => array(
                'bool'      => 'boolean',
                'int'       => 'number',
                'double'    => 'number',
                'float'     => 'number',
                'void'      => 'undefined',
                'string'    => 'string',
                'ISupports' => 'Node'
            )
        ));
    }
}