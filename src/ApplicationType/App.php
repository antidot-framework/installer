<?php

declare(strict_types=1);

namespace Antidot\Installer\ApplicationType;

interface App
{
    public function install(): void;
}
