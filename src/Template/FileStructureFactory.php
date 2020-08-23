<?php

declare(strict_types=1);

namespace Antidot\Installer\Template;

interface FileStructureFactory
{
    public function create(string $installationPath): void;
}
