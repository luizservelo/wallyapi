#!/usr/bin/env php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Console\Commands\Make;

$make = new Make();
$make->run($argv); 