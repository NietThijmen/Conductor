<?php

namespace App\Mcp\Tools;

use App\Models\ChangelogChange;
use App\Models\Package;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('A description of what this tool does.')]
class GetChangelogs extends Tool
{
    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $packageName = $request->string('package');
        $version = $request->string('version');

        $package = Package::where('name', $packageName)->first();
        if (! $package) {
            return Response::text("Could not find package with name: {$packageName}, use the findPackage tool");
        }

        $changelog = $package->changelogs()->where('new_version', $version)->first();

        if (! $changelog) {
            return Response::text("Could not find changelog for version: {$version} use the findVersions tool");
        }

        $changelog->load('changes');

        $changes = $changelog->changes->map(function (ChangelogChange $changelogChange) {
            return [
                'type' => $changelogChange->type->value,
                'title' => $changelogChange->title,
                'message' => $changelogChange->message,
            ];
        });

        return Response::text($changes->toPrettyJson());
    }

    /**
     * Get the tool's input schema.
     *
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'package' => $schema->string()->required(),
            'version' => $schema->string()->required(),
        ];
    }
}
