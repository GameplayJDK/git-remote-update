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

use App\Console\Helper\FormatterHelper;
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
     * @var FormatterHelper
     */
    private $formatter;

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
        $this->setFormatter();

        /** @var string|null $path */
        $path = $input->getArgument('path');

        // If this happens, I really don't know what went wrong here. ／人◕ __ ◕人＼
        if (null === $path) {
            $output->writeln(
                $this->formatter->formatBlock([
                    'Unable to read the configuration file!',
                    'The file path was neither given as argument nor as property.',
                ], 'error')
            );

            return 1;
        }

        // There must be something wrong with permissions! (/¯◡ ‿ ◡)/¯ ~ ┻━┻
        if (!is_readable($path)) {
            $output->writeln(
                $this->formatter->formatBlock([
                    'Unable to read the configuration file!',
                    "The file either does not exist at '{$path}' or is not readable.",
                ], 'error')
            );

            return 1;
        }

        /** @var string|null $json */
        $json = file_get_contents($path) ?: null;

        // Something else must be wrong with the file. (⊙＿⊙')
        if (null === $json) {
            $output->writeln(
                $this->formatter->formatBlock([
                    'Unable to read the configuration file!',
                    'The file content is invalid or could not be read.',
                ], 'error')
            );

            return 1;
        }

        /** @var array|null $data */
        $data = json_decode($json, true) ?: null;

        // Yes, you came this far, but know your json, dude. O=('-'Q)
        if (null === $data || JSON_ERROR_NONE !== json_last_error()) {
            $lastErrorMessage = json_last_error_msg();

            $output->writeln(
                $this->formatter->formatBlock([
                    'Unable to decode json from the configuration file!',
                    'The file content likely to contain errors due to invalid json.',
                    "The last error message was: '{$lastErrorMessage}'.",
                ], 'error')
            );

            return 1;
        }

        // Well, this would be stupid... (≧︿≦)
        if (!is_array($data)) {
            $output->writeln(
                $this->formatter->formatBlock([
                    'Unable to process data from the configuration file!',
                    'The json content is not an array.',
                ], 'error')
            );

            return 1;
        }

        // Won't need labels from here on. Never wanted this label feature anyway, bro. (꒡⌓꒡)
        $data = array_values($data);

        // Clean up after ourselves. (⌐⊙_⊙)
        unset($path);

        /** @var array $outputOfShell */
        $outputOfShell = [];

        /** @var string $path */
        foreach ($data as $path) {
            // This seems kind of wrong, does it? (눈_눈)
            if (!is_string($path)) {
                $output->writeln(
                    $this->formatter->formatSection('INFO', "The given path is no string: '{$path}'! Skipping...", 'info')
                );

                continue;
            }

            // You screwed up the configuration, obviously. ε(´סּ︵סּ`)з
            if (!is_dir($path)) {
                $output->writeln(
                    $this->formatter->formatSection('INFO', "The given path does not exist: '{$path}'! Skipping...", 'info')
                );

                continue;
            }

            $outputOfShell[$path] = `cd {$path} && git remote update` ?: null;
        }

        // Finish by yelling out the result. ┬──┬ /(ò_ó/)
        $output->writeln(
            $this->generate($outputOfShell)
        );

        return 0;
    }

    private function setFormatter(): void
    {
        $this->formatter = $this->getHelper('formatter');
    }

    /**
     * @param array $outputOfShell
     * @return iterable
     */
    private function generate(array $outputOfShell): iterable
    {
        // I just wanted to use a generator function, so why not? ¯\_(ツ)_/¯
        yield from array_map(function (string $key, ?string $value): string {
            // This will happen, when the value is null. (ㆆ-ㆆ)
            if (null === $value) {
                return $this->formatter
                    ->formatBlockSection('ERR', [
                        "Output of command execution in '{$key}':",
                        'null',
                    ], 'error');
            }

            // So, if we only hat Php 7.4, we could simply use the spread operator... Would be much nicer to the eye
            // than this! And I still want to use the argument unpacking! ⊂(◉‿◉)つ
            $messageArray = explode("\n", $value);
            array_unshift($messageArray, ...[
                "Output of command execution in '{$key}':",
            ]);

            // TODO: In 7.4 use the spread operator like this:
//            $messageArray = [
//                "Output of command execution in '{$key}':",
//                ...$messageArray,
//            ];

            return $this->formatter
                ->formatBlockSection('INFO', $messageArray, 'info');
        }, array_keys($outputOfShell), array_values($outputOfShell));
    }

    /**
     * @return string|null
     */
    public function getDefaultPath(): ?string
    {
        return $this->defaultPath;
    }
}
