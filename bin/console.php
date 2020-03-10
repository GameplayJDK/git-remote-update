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
use Symfony\Component\Console\Application;

require dirname(__DIR__) . '/vendor/autoload.php';

// TODO: Add more ASCII emoticons from http://asciimoji.com/.
// TODO: Make the label be printed out inside the loop; print the path as well, event when no error occurred.
// TODO: Also make the output more beautiful... Currently it just looks horrible -
// TODO: Maybe a bit of unit-testing? But on the other hand, this is just a single-command app, so we'll see..

$command = new GitRemoteUpdateCommand();
$application = new Application();
$application->add($command);
$application->setDefaultCommand($command->getName(), true);
$application->run();
