<?php

declare(strict_types=1);

namespace Antidot\Installer;

class RunInstall
{
    /**
     * @param string $command
     * @param array<mixed>|null $output
     * @param null|int $returnVar
     * @return string
     */
    public function exec(string $command, array &$output = null, int &$returnVar = null): string
    {
        return \exec($command, $output, $returnVar);
    }
}
