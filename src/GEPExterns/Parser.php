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
    private $jsClasses = array();

    /**
     * @var array
     */
    private $parents = array();

    /**
     * @var Crawler[]
     */
    private $crawlerCache = array();

    /**
     * @param Client $client The HTTP client
     * @param string $url    The base url
     */
    public function __construct(Client $client, $url = "https://developers.google.com/earth/documentation/reference/")
    {
        $this->url = $url;
        $this->client = $client;
    }

    /**
     * Parse the docs
     */
    public function parse()
    {
        $this->addClasses();
        foreach($this->jsClasses as $jsClass) {
            $this->addMethods($jsClass);
            $this->addProperties($jsClass);
            $this->addParents($jsClass);
        }
        sort($this->jsClasses);
        return new Tree($this->jsClasses);
    }

    /**
     * Add the classes
     */
    private function addClasses()
    {
        $that = $this;

        $this->getCrawler($this->url, '#doxygen-ref')
            ->filter('a.el')
            ->each(function($node) use($that) {
                /** @var $node \DomNode */
                $that->jsClasses[$node->textContent] = new JsClass($node->textContent, $node->attributes->getNamedItem('href')->nodeValue);
            })
        ;
    }

    /**
     * Adds the method of the given class.
     *
     * @param JsClass $jsClass
     */
    private function addMethods(JsClass $jsClass)
    {
        $docLink = $this->getCrawler($this->url, '#doxygen-ref')
            ->selectLink($jsClass->getName())
            ->link()
        ;

        $this->getCrawler($docLink->getUri(), 'div.contents')
            ->filter('a.el')
            ->each(function($node) use($jsClass) {
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

    /**
     * Adds the properties of the given class.
     *
     * @param JsClass $jsClass
     */
    private function addProperties(JsClass $jsClass)
    {
        $docLink = $this->getCrawler($this->url, '#doxygen-ref')
            ->selectLink($jsClass->getName())
            ->link()
        ;

        $this->getCrawler($docLink->getUri(), 'div.contents')
            ->filter('td.memItemRight')
            ->each(function($node) use($jsClass) {
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
        });
    }

    /**
     * Adds the parents for the given class.
     *
     * @param JsClass $jsClass
     */
    private function addParents(JsClass $jsClass)
    {
        $docLink = $this->getCrawler($this->url, '#doxygen-ref')
            ->selectLink($jsClass->getName())
            ->link()
        ;

        $linkNodes = $this->client
            ->click($docLink)
            ->selectLink('List of all members.')
        ;

        if (1 === count($linkNodes)) {
            $this->client
                ->click($linkNodes->link())
                // see https://github.com/symfony/symfony/issues/6126
                ->filter('div.contents')
                ->filter('td:nth-child(2n)')
                ->each(function($node) use($jsClass) {
                    $jsClass->addParent($node->textContent);
                })
            ;
        }
    }

    /**
     * Returns a crawler for the specified URL. Crawler are locally cached.
     *
     * @param string $url
     * @param string $filter
     * @return Crawler
     */
    private function getCrawler($url, $filter = '')
    {
        $key = md5($url . $filter);

        if (!isset($this->crawlerCache[$key])) {
            $crawler = $this->client->request('GET', $url);
            if ('' !== $filter) {
                $crawler->filter($filter);
            }
            $this->crawlerCache[$key] = $crawler;
        }

        return clone $this->crawlerCache[$key];
    }
}