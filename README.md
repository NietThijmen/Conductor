# Composer Changelogger

Automatically generated changelogs for your Composer dependency updates, powered by AI.

When you update a dependency like `laravel/framework` or `roots/wordpress-no-content`, Composer Changelogger diffs the file system between the old and new versions and uses AI agents (via Laravel AI SDK) to produce a structured changelog with BREAKING, NEW, and UPDATED categories.

## Features

- **Track any package** — administrators add packages to a watchlist; no composer.lock coupling
- **Automated changelogs** — no more digging through release notes or commit logs
- **AI-powered analysis** — uses Laravel AI SDK agents to understand what changed
- **File-system diffing** — compares old and new package versions at the file level
- **Structured output** — categorized as BREAKING, NEW, and UPDATED changes
- **Multi-provider AI** — works with OpenAI, Anthropic, Gemini, Ollama, and more
- **MCP server** — AI coding assistants (Cursor, Claude Code, etc.) can query updates and changelogs directly

## Installation

```bash
composer require laravel/ai
composer setup
```

Configure your preferred AI provider in `.env`:

```env
AI_PROVIDER=openai
OPENAI_API_KEY=sk-...
```

Or use Anthropic, Gemini, Ollama, etc. See the [Laravel AI SDK](https://github.com/laravel/ai) for all supported providers.

## Usage

Add a package to the watchlist:

```bash
php artisan package:track laravel/framework --version 11.0.0
```

That's it. Every hour (configurable), the scheduler checks all tracked packages for new versions. When an update is found, a queue job is dispatched to download both versions, diff them, and generate an AI-powered changelog — all automatically.

Public changelogs at `/changelogs/{package}` — no login required. Manage your watchlist on the authenticated dashboard at `/admin/packages`.

## AI Agent Integration (MCP)

Composer Changelogger ships with an **MCP server** that AI coding assistants can connect to. This allows agents to autonomously check for updates and retrieve changelogs.

### Exposed MCP Tools

| Tool | Description |
|------|-------------|
| `list_packages` | List all tracked packages and their current versions |
| `check_updates` | Check Packagist for newer versions of tracked packages |
| `get_changelog` | Get changelog for a package between two versions (omit `new_version` for latest) |
| `get_pending_updates` | List packages with available updates that lack a changelog |

### Example Agent Workflow

An AI assistant (e.g. Claude Code, Cursor) can:

1. Call `check_updates` to see what packages have newer versions (changelogs already generated in the background)
2. Call `get_changelog` for each update to understand what changed
3. Assess risk based on BREAKING vs NEW vs UPDATED categories
4. Propose applying the updates with confidence

### Usage

Configure a client's `.mcp.json` or `~/.cursor/mcp.json`:

```json
{
  "mcpServers": {
    "composer-changelogger": {
      "command": "php",
      "args": ["artisan", "mcp:serve"]
    }
  }
}
```

## How It Works

1. Administrators add packages to a tracked watchlist (name + current version)
2. A scheduled task runs hourly and polls Packagist for new versions of every tracked package
3. When a new version is detected, a queue job is dispatched to download and extract both the old and new package versions
4. A file-system diff is performed between the versions
5. The diff is sent to an AI agent for analysis
6. The structured changelog (BREAKING, NEW, UPDATED) is stored and the tracked package version is updated
7. Changelogs are publicly accessible at `/changelogs/{package}` and via the MCP server — no login required. Package management stays behind authentication

## Requirements

- PHP 8.3+
- Laravel 13.x
- Composer
- Node 22+ (for frontend build)
- An AI provider API key (OpenAI, Anthropic, etc.)

## Testing

```bash
composer test
```

## License

MIT