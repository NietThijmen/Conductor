<?php

namespace App\Mcp\Servers;

use App\Mcp\Tools\FindPackage;
use App\Mcp\Tools\FindVersions;
use App\Mcp\Tools\GetChangelogs;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

#[Name('Changelog')]
#[Version('1.0.0')]
#[Instructions('Use this tools to check for automatic changelogs for composer packages')]
class ChangelogServer extends Server
{
    protected array $tools = [
        FindPackage::class,
        FindVersions::class,
        GetChangelogs::class
    ];

    protected array $resources = [
        //
    ];

    protected array $prompts = [
        //
    ];
}
