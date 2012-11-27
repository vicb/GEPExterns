<?php
/*
 * Copyright 2012 Victor Berchet <victor@suumit.com>
 *
 * Licensed under the MIT License
 */

use GEPExterns\Parser;
use GEPExterns\ClosureDumper;
use Guzzle\Http\Client;

require 'vendor/autoload.php';

$parser = new Parser(new Client(), __DIR__.'/cache/parser');
$tree = $parser->parse();
$dumper = new ClosureDumper($tree);
$dumper->dump(__DIR__.'/cache/twig');