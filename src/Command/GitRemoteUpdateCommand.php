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

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class GitRemoteUpdateCommand
 * @package App\Command
 */
class GitRemoteUpdateCommand extends Command
{
    const ARGUMENT_PATH_NAME = 'path';
    const ARGUMENT_PATH_DESCRIPTION = 'The path to the configuration file.';

    /**
     * @var string
     */
    protected static $defaultName = 'app:git-remote-update';

    /**
     * @var string|null
     */
    private $defaultPath;

    /**
     * GitRemoteUpdateCommand constructor.
     * @param string|null $defaultPath
     */
    public function __construct(?string $defaultPath = null)
    {
        $this->defaultPath = $defaultPath;

        parent::__construct(null);
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setDescription('A tool for running `git remote update` on multiple directories.')
            ->addArgument(static::ARGUMENT_PATH_NAME, InputArgument::OPTIONAL, static::ARGUMENT_PATH_DESCRIPTION, $this->getDefaultPath());
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var string|null $path */
        $path = $input->getArgument('path');

        if (null === $path) {
            $output->writeln([
                '', "<error>Could not read configuration file at 'null'!</error>",
                '', "<error>The file path was neither given as argument nor as property.</error>",
            ]);

            return 1;
        }

        if (!is_readable($path)) {
            $output->writeln([
                '', "<error>Could not read configuration file at '$path'!</error>",
                '', "<error>The file does not exist or is not readable.</error>",
            ]);

            return 1;
        }

        /** @var string|null $json */
        $json = file_get_contents($path) ?: null;

        if (null === $json) {
            $output->writeln([
                '', "<error>Could not read configuration file at '$path'!</error>",
                '', "<error>The file content is invalid or could not be read.</error>",
            ]);

            return 1;
        }

        /** @var array|null $data */
        $data = json_decode($json, true) ?: null;

        if (null === $data || JSON_ERROR_NONE !== json_last_error()) {
            $lastErrorMessage = json_last_error_msg();

            $output->writeln([
                '', "<error>Could not decode json from configuration file at '$path'!</error>",
                '', "<error>The file content is either empty, invalid json or contains errors.</error>",
                '', "<error>The last error message was: $lastErrorMessage.</error>",
            ]);

            return 1;
        }

        if (!is_array($data)) {
            $output->writeln([
                '', "<error>Could not decode json from configuration file at '$path'!</error>",
                '', "<error>The json content is not an array.</error>",
            ]);

            return 1;
        }

        $data = array_values($data);

        unset($path);

        /** @var array $outputOfShell */
        $outputOfShell = [];

        /** @var string $path */
        foreach ($data as $path) {
            if (!is_string($path)) {
                $output->writeln([
                    '', "<error>The given path is no string: '$path'!</error>",
                ]);

                continue;
            }

            if (!is_dir($path)) {
                $output->writeln([
                    '', "<error>The given path does not exist: '$path'!</error>",
                ]);

                continue;
            }

            $outputOfShell[$path] = `cd $path && git remote update` ?: null;
        }

        /**
         * I just wanted to use a generator function, so why not? ¯\_(ツ)_/¯
         *
         * @param array $outputOfShell
         * @return iterable
         */
        function generate(array $outputOfShell): iterable
        {
            yield from array_map(function (string $key, ?string $value): string {
                if (null === $value) {
                    return "<error>Output of command execution in '$key': null</error>";
                }

                return "<info>Output of command execution in '$key': $value</info>";
            }, array_keys($outputOfShell), array_values($outputOfShell));
        }

        $output->writeln(generate($outputOfShell));

        return 0;
    }

    /**
     * @return string|null
     */
    public function getDefaultPath(): ?string
    {
        return $this->defaultPath;
    }
}
