# Conductor

**AI-generated changelogs for Composer packages.**

Conductor watches your PHP dependencies and tells you what actually changed between versions — before you run `composer update`. Every changelog is generated from the real file-system diff, then categorized into **Breaking**, **New**, and **Updated** changes by an AI agent.

No more digging through sparse release notes or diffing tags by hand.

## What it does

- **Tracks** Composer packages from Packagist and private registries.
- **Detects** new versions automatically on a scheduled poll.
- **Downloads** both the old and new package versions.
- **Diffs** the file system recursively.
- **Generates** a structured, human-readable changelog with the Laravel AI SDK.
- **Publishes** public changelog pages and exposes the same data through an API and MCP server.

## Who it's for

- **PHP developers** about to update dependencies and wondering what will break.
- **AI coding agents** (Cursor, Claude Code, GitHub Copilot) querying changelogs through the MCP server.
- **Teams** that want a single, machine-readable source of truth for dependency changes.

## Quick start

```bash
composer run setup
php artisan serve
```

Visit `http://localhost:8000` to browse changelogs, or log in to manage tracked packages.

## Built with

- Laravel 13
- Livewire 4 + Flux 2
- Laravel AI SDK
- Tailwind CSS 4
- Pest

## License

MIT
