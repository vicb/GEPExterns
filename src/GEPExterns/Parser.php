<?php
/*
 * Copyright 2012 Victor Berchet <victor@suumit.com>
 *
 * Licensed under the MIT License
 */

namespace GEPExterns;

use Symfony\Component\DomCrawler\Crawler;
use Goutte\Client;


class Parser
{
    /**
     * @var string The base url
     */
    private $url;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var array The classes
     */
    private $classes = array();

    /**
     * @var array
     */
    private $parents = array();

    /**
     * @param Client $client The HTTP client
     * @param string $url    The base url
     */
    public function __construct(Client $client, $url = "https://developers.google.com/earth/documentation/reference/")
    {
        $this->url = $url;
        $this->client = $client;
    }

    public function parse()
    {
        $this->getClasses();
        foreach($this->classes as $jsClass) {
            $this->addMethods($jsClass);
            $this->addProperties($jsClass);
            $this->addParents($jsClass);
        }
        $this->findParents();
    }

    public function dump($cacheDir)
    {
        $loader = new \Twig_Loader_Filesystem(__DIR__.'/templates');
        $twig = new \Twig_Environment($loader, array(
            'cache'       => $cacheDir,
            'auto_reload' => true
        ));
        echo $twig->render('externs.twig', array(
            'classes' => $this->classes,
            'parents' => $this->parents,
            'typeMap' => array(
                'bool'   => 'boolean',
                'int'    => 'number',
                'double' => 'number',
                'float'  => 'number',
                'void'   => 'undefined',
                'string' => 'string'
            )
        ));
    }

    private function findParents()
    {
        $nbParents = array();
        $toFind = array();

        foreach ($this->classes as $jsClass) {
            $count = count($jsClass->getParents());
            $nbParents[$jsClass->getName()] = 0;
            if (0 === $count) {
                $this->parents[$jsClass->getName()] = null;
            } else {
                $toFind[] = $jsClass;
            }
        }

        foreach ($toFind as $jsClass) {
            $className = $jsClass->getName();
            $nbClassParents = isset($nbParents[$className]) ? $nbParents[$className] : 0 ;
            $parent = null;
            foreach ($jsClass->getParents() as $className) {
                if ($nbParents[$className] < $nbClassParents) {
                    $nbClassParents = $nbParents[$className];
                    $parent = $className;
                }
            }
            $this->parents[$jsClass->getName()] = $parent;
        }
    }

    private function getClasses()
    {
        $that = $this;
        $todo = 0;

        $this->client->request('GET', $this->url)
            ->filter('a.el')
            ->each(function($node) use($that, $todo) {
                /** @var $node \DomNode */
                $that->classes[] = new JsClass($node->textContent, $node->attributes->getNamedItem('href')->nodeValue);
            })
        ;
    }

    private function addMethods(JsClass $jsClass)
    {
        $that = $this;

        $docLink = $this->client->request('GET', $this->url)
            ->selectLink($jsClass->getName())
            ->link();

        $this->client
            ->click($docLink)
            ->filter('div.contents a.el')
            ->each(function($node) use($that, $jsClass) {
                if (preg_match('/(?<method>[\w-.]+) \((?<args>.*?)\)/', $node->parentNode->textContent, $info)) {
                    // method & args
                    $arguments = array();
                    $args = explode(', ', $info['args']);
                    if (false !== $args) {
                        foreach ($args as $arg) {
                            $argInfo = explode(' ', $arg);
                            if (count($argInfo) >= 2) {
                                $arguments[] = new Variable(trim($argInfo[1]), trim($argInfo[0]));
                            }
                        }
                    }
                    // return value
                    $rvCrawler = new Crawler($node->parentNode->parentNode);
                    if (1 === count($rvCrawler->filter('.memItemLeft'))) {
                        $jsClass->addMethod(
                            new Method(
                                $info['method'],
                                $rvCrawler->filter('.memItemLeft')->eq(0)->text(),
                                $arguments
                            )
                        );
                    }
                }
            })
        ;
    }

    private function addProperties(JsClass $jsClass)
    {
        $that = $this;

        $docLink = $this->client->request('GET', $this->url)
            ->selectLink($jsClass->getName())
            ->link();

        $this->client
            ->click($docLink)
            ->filter('div.contents td.memItemRight')
            ->each(function($node) use($that, $jsClass) {
            if (preg_match('/^(?<property>[\w-.]+)$/', $node->textContent, $info)) {
                $typeCrawler = new Crawler($node->parentNode);
                if (1 == count($typeCrawler->filter('.memItemLeft'))) {
                    $jsClass->addProperty(
                        new Variable(
                            $info['property'],
                            str_replace('readonly', '', $typeCrawler->filter('.memItemLeft')->eq(0)->text())
                        )
                    );
                }
            }
        })
        ;
    }

    private function addParents(JsClass $jsClass)
    {
        $that = $this;

        $docLink = $this->client->request('GET', $this->url)
            ->selectLink($jsClass->getName())
            ->link();

        $linkNodes = $this->client
            ->click($docLink)
            ->selectLink('List of all members.')
        ;

        if (1 === count($linkNodes)) {
            $this->client
                ->click($linkNodes->link())
                ->filter('div.contents td:nth-child(2) a.el')
                ->each(function($node) use($that, $jsClass) {
                    $jsClass->addParent($node->textContent);
                })
            ;
        }
    }
}