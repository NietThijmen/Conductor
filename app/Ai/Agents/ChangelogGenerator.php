<?php

namespace App\Ai\Agents;

use App\Enums\ChangelogChangeType;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Messages\Message;
use Laravel\Ai\Promptable;
use Stringable;

class ChangelogGenerator implements Agent, Conversational, HasStructuredOutput
{
    use Promptable;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return <<<'INSTRUCTIONS'
You are an expert software release analyst. Your job is to analyze the provided unified diff between two versions of a Composer package and produce a structured, human-readable changelog.

For each meaningful change, categorize it as one of:
- breaking: backward-incompatible changes such as removed methods/classes, changed signatures, renamed config keys, or dropped dependencies.
- new: newly added features, classes, methods, public APIs, configuration options, or dependencies.
- updated: behavioral changes, improvements, bug fixes, internal refactoring, performance changes, or deprecations.

Provide:
1. A concise title for the overall changelog (one sentence).
2. A one-paragraph summary of the update.
3. A list of individual changes. Each change must have a type, a short title, and a brief message explaining what changed and why it matters.

Be specific and cite file paths when relevant. Ignore version bumps, lock files, build artifacts, and generated files unless they are meaningful to the consumer of the package.
INSTRUCTIONS;
    }

    /**
     * Get the list of messages comprising the conversation so far.
     *
     * @return Message[]
     */
    public function messages(): iterable
    {
        return [];
    }

    /**
     * Get the agent's structured output schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'title' => $schema->string()->required(),
            'summary' => $schema->string()->required(),
            'changes' => $schema->array()
                ->items($schema->object(fn (JsonSchema $schema) => [
                    'type' => $schema->string()->enum(ChangelogChangeType::class)->required(),
                    'title' => $schema->string()->required(),
                    'message' => $schema->string()->required(),
                ]))
                ->required(),
        ];
    }
}
