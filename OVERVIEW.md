# Composer Changelogger — Overview

## Vision

Composer Changelogger gives developers peace of mind when updating dependencies. Instead of blindly running `composer update` and hoping nothing breaks, you get a human-readable, AI-generated changelog of exactly what changed — broken down into BREAKING, NEW, and UPDATED categories — before you commit or deploy.

## Architecture

### Stack

| Layer | Technology |
|-------|-----------|
| PHP | 8.4 |
| Framework | Laravel 13.x |
| Frontend | Livewire 4.x + Flux 2.x UI |
| AI SDK | Laravel AI SDK (`laravel/ai`) |
| CSS | Tailwind CSS 4.x |
| Build | Vite 8.x |
| Database | SQLite / MySQL / PostgreSQL |
| Testing | Pest 5.x |
| Static analysis | PHPStan level 7 |

### Flow

```
  Admin adds package to watchlist (name + current version)
         │
         ▼
  Scheduler (hourly) ──→ Polls Packagist for each tracked package
         │
         ▼
  [New version detected] ──→ Dispatches GenerateChangelog job to queue
         │
         ▼
  Queue Worker ──→ Downloads & extracts old and new versions
         │
         ▼
  File System Differ ──→ Recursive diff of source files
         │
         ▼
  AI Agent ───────────→ Analyzes diff, categorizes changes
         │
         ▼
Changelog Store ────→ Persists structured changelogs,
                         updates tracked package version
          │
          ├──────────────────────────┬───────────────────┐
          ▼                          ▼                   ▼
  Public Changelog Pages     Admin Dashboard       MCP Server
  (no auth required)        (auth required)    (AI assistants query
  /changelogs/{package}     /admin/*            changelogs & updates)
```

### AI Agent Integration via MCP

Composer Changelogger doubles as an **MCP server** (Model Context Protocol), allowing AI coding assistants — Cursor, Claude Code, GitHub Copilot, and others — to query package updates and their changelogs directly. An agent can ask:

> "What packages need updating?" → Returns tracked packages where a newer version exists on Packagist
> "What changed in laravel/framework between 11.0.0 and 12.0.0?" → Returns the structured changelog
> "Give me changelogs for all pending updates" → Returns changelogs for every package with a newer version available

This enables fully autonomous workflows: an AI agent audits your dependencies, retrieves changelogs, assesses risk, and proposes updates — all through MCP tools.

### Key Components (Planned)

**`app/Http/Controllers/` — Web routes**

- **`ChangelogController`** — Public routes (`/changelogs`, `/changelogs/{package}`) for browsing changelogs. No authentication required
- **`PackageController`** — Authenticated routes (`/admin/packages`) for managing the watchlist. Requires login via Laravel Fortify

**`app/Changelog/` — Core domain**

- **`VersionDiff`** — Represents a detected version change between two snapshots
- **`PackageFetcher`** — Downloads and extracts old/new package versions from Packagist, Git tags, or local cache
- **`FileSystemDiffer`** — Produces a structured diff of the file system between two package versions (added, removed, modified files with line-level diffs)
- **`ChangelogGenerator`** — Coordinates the pipeline: diff → AI analysis → structured output

**`app/Ai/Agents/` — AI agents (Laravel AI SDK)**

- **`ChangelogAgent`** — An AI agent that receives the file-system diff and returns a structured changelog with `BREAKING`, `NEW`, and `UPDATED` categories
- Uses `Text::generate()` with structured output schemas to guarantee consistent formatting

**`app/Models/`**

- **`Package`** — Eloquent model for the admin-managed watchlist. Stores the package name (e.g. `laravel/framework`) and the currently tracked version. Administrators add and update these records
- **`Changelog`** — Eloquent model storing generated changelogs per package version
- **`Dependency`** — Tracks watched packages and their version history

**`app/Jobs/` — Queue jobs**

- **`GenerateChangelog`** — Dispatched when a new version is detected. Downloads both package versions, diffs them, runs the AI agent, and persists the resulting changelog. Ran on the queue so generation is async and can be retried on failure

**`app/Console/Commands/` — Artisan commands**

- **`package:track`** — Add a package to the watchlist with its current version
- **`package:list`** — List all tracked packages
- **`package:untrack`** — Remove a package from the watchlist

**`app/Console/Kernel.php` — Scheduled tasks**

- **`packages:check-for-updates`** — Runs on a configurable schedule (default: hourly). Iterates all tracked packages, queries Packagist for the latest version, and dispatches `GenerateChangelog` jobs for any that have a newer release

**`app/Mcp/` — MCP server**

- **`McpServer`** — A Laravel-based MCP server exposing tools for AI agents
- **`Tools/ListPackages`** — Returns all tracked packages and their current versions
- **`Tools/CheckUpdates`** — Queries Packagist for newer versions of tracked packages
- **`Tools/GetChangelog`** — Returns the changelog between two versions (or old → latest if no new version specified)
- **`Tools/GetPendingUpdates`** — Lists packages with available updates that don't yet have a changelog

### AI Analysis

The core intelligence comes from the Laravel AI SDK. A `ChangelogAgent` agent is prompted with:

1. The package name and version change (e.g., `laravel/framework` from `11.0.0` to `12.0.0`)
2. The structured file-system diff between versions
3. Instructions to categorize changes into:
   - **BREAKING** — Backward-incompatible changes (signature changes, removed methods, config structure changes)
   - **NEW** — New features, classes, methods, configuration options
   - **UPDATED** — Behavioral changes, deprecations, internal refactoring

The agent returns a structured JSON response that maps directly to the changelog display.

The agent may also include:
- A concise summary of the overall update
- Relevant file paths for each change
- Suggested migration steps for breaking changes

### AI Provider Flexibility

Thanks to the Laravel AI SDK, any supported provider can be used:

- OpenAI (GPT-4o, etc.)
- Anthropic (Claude Opus 4, Sonnet)
- Google Gemini
- Ollama (local models)
- DeepSeek, Mistral, Groq, and more

This allows developers to choose between cost-effective local models and more capable cloud models depending on their needs.

## Current Status

This project is in early development on top of the Laravel Livewire Starter Kit. The scaffolding (authentication, user settings, dashboard) is in place. The core changelog domain and AI integration are being built.

## Roadmap

1. **Core engine** — Package model, watchlist management, package fetching, file-system diffing, queue jobs
2. **AI integration** — ChangelogAgent using Laravel AI SDK with structured output
3. **Scheduling & automation** — Cron-based polling for new versions, auto-dispatch of changelog generation
4. **UI** — Public changelog browsing, authenticated package management, search and compare
5. **MCP server** — Expose read-only tools for AI agents to query updates and changelogs
6. **Notifications** — Email/Slack/PR comment integration when updates are detected
7. **Team features** — Shared changelogs, approval workflows, audit trails