<?php
/*
 * Copyright 2012 Victor Berchet <victor@suumit.com>
 *
 * Licensed under the MIT License
 */

use GEPExterns\Parser;
use Goutte\Client;

require 'vendor/autoload.php';

$parser = new Parser(new Client());

$parser->parse();

$parser->dump(__DIR__.'/cache');