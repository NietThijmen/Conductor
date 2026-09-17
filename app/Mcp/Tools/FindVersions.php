<?php

namespace App\Mcp\Tools;

use App\Models\Changelog;
use App\Models\Package;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;

#[Name('findVersions')]
#[Description('A description of what this tool does.')]
class FindVersions extends Tool
{
    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $packageName = $request->string('package');
        $package = Package::where('name', $packageName)->first();

        if (! $package) {
            return Response::text("Could not find package with name: {$packageName}, use the findPackage tool");
        }

        $versions = $package->changelogs->map(function (Changelog $changelog) use ($package) {
            return [
                'package' => $package->name,
                'version' => $changelog->new_version,
                'date' => $changelog->created_at->format('Y-m-d'),
            ];
        });

        return Response::text($versions->toPrettyJson());
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
        ];
    }
}
