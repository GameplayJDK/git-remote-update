<?php
/**
 * The MIT License (MIT)
 * Copyright (c) 2020 GameplayJDK
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated
 * documentation files (the "Software"), to deal in the Software without restriction, including without limitation the
 * rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the
 * Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 * COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR
 * OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

// INFO: The single command application is documented here:
// https://symfony.com/doc/current/components/console/single_command_tool.html

use App\Command\GitRemoteUpdateCommand;
use App\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Application;

require dirname(__DIR__) . '/vendor/autoload.php';

// TODO: Maybe a bit of unit-testing? But on the other hand, this is just a single-command app, so we'll see..

$path = dirname(__DIR__) . '/configuration.json';
$command = new GitRemoteUpdateCommand($path);
$application = new Application();
$application->getHelperSet()
    ->set(new FormatterHelper());
$application->add($command);
$application->setDefaultCommand($command->getName(), true);
$application->run();
