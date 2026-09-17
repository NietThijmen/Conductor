<?php

namespace App\Mcp\Tools;

use App\Models\Package;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;

#[Name('findPackage')]
#[Description('Look for a indexed package by name')]
class FindPackage extends Tool
{
    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $query = $request->string('query');
        $packages = Package::where('name', 'like', "%{$query}%")
            ->limit(10)
            ->get()
            ->map(function (Package $package) {
                return [
                    'id' => $package->id,
                    'name' => $package->name,
                    'version' => $package->current_version,
                ];
            });

        if ($packages->count() === 0) {
            return Response::text("Could not find any packages matching the query: {$query}, maybe try to be less specific.");
        }

        $packagesOutput = $packages->toPrettyJson();

        if ($packages->count() === 10) {
            $packagesOutput = "You searched for {$query}, but there are more than 10 results. Please be more specific.\n\n".$packagesOutput;
        }

        return Response::text($packagesOutput);
    }

    /**
     * Get the tool's input schema.
     *
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'query' => $schema->string()->required(),
        ];
    }
}
