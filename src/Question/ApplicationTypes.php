<?php

declare(strict_types=1);

namespace Antidot\Installer\Question;

class ApplicationTypes
{
    public const QUESTION = 'What type of application you want to install? [<info>0</info>] ';
    public const WEB_APP = 'Classic Web App';
    public const SERVERLESS_APP = 'Serverless App';
    public const CLI_APP = 'Console Line Tool';
    public const MICRO_APP = 'Micro Http App';
    public const REACT_APP = 'React Http App';
    public const OPTIONS = [
        self::WEB_APP,
        self::SERVERLESS_APP,
        self::CLI_APP,
        self::MICRO_APP,
        self::REACT_APP,
    ];
}
