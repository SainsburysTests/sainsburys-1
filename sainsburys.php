#!/usr/bin/env php
<?php
require_once('vendor/autoload.php');
require_once('./classes/CommandLine.class.php');
use Symfony\Component\Console\Application;

$app = new Application();
$app->add(new CommandLine());
$app->run();
