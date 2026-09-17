# Roadmap

## Phase 1 — Core Engine

*Build the foundation: package watchlist, file diffing, and queue jobs.*

- [ ] **`Package` model & migration** — Database table for the admin-managed watchlist with fields: `name` (e.g. `laravel/framework`), `current_version`, `provider` (Packagist or custom URL), and timestamps
- [ ] **`package:track` command** — Artisan command to add a package to the watchlist: `php artisan package:track laravel/framework --version 11.0.0`
- [ ] **`package:list` command** — List all tracked packages with their current versions
- [ ] **`package:untrack` command** — Remove a package from the watchlist
- [ ] **Package fetcher** — Download and extract old and new package versions from Packagist (or fall back to GitHub tags). Cache extracted packages to avoid redundant downloads
- [ ] **File-system differ** — Recursively diff the source files between old and new package versions. Track added, removed, and modified files with line-level diffs
- [ ] **`GenerateChangelog` job** — Queue job that receives a package name and new version, diffs against the stored current version, and outputs a raw diff (pre-AI). Dispatched whenever a new version is detected

## Phase 2 — AI Integration

*Bring in the Laravel AI SDK to turn diffs into structured changelogs.*

- [ ] **Install & configure `laravel/ai`** — Wire up the preferred AI provider(s) via `.env`
- [ ] **`ChangelogAgent`** — Create an AI agent that receives the file-system diff and returns a structured changelog. The agent categorizes changes into BREAKING, NEW, and UPDATED
- [ ] **Structured output schema** — Define a strict output schema so the agent always returns consistent, parseable JSON
- [ ] **Migration suggestions** — Have the agent include migration steps for breaking changes where possible
- [ ] **Provider switching** — Allow users to choose between OpenAI, Anthropic, Gemini, Ollama, etc. per run or per project
- [ ] **Wire into `GenerateChangelog` job** — The queue job calls the AI agent and persists the structured changelog, then updates the tracked package's `current_version`

## Phase 3 — Scheduling & Automation

*Changelogs generate themselves — no manual interaction needed.*

- [ ] **`packages:check-for-updates` command** — Artisan command that iterates all tracked packages, queries Packagist for the latest version, and dispatches `GenerateChangelog` jobs for any with a newer release
- [ ] **Scheduled task** — Register the command in the Laravel scheduler (default: hourly, configurable via config)
- [ ] **Queue configuration** — Document required queue setup (database, Redis, etc.) to run the `GenerateChangelog` jobs
- [ ] **Failure handling** — Failed jobs retry with exponential backoff; notify admins after max retries are exhausted
- [ ] **Concurrency guard** — Ensure only one `packages:check-for-updates` runs at a time (via `withoutOverlapping`)

## Phase 4 — Data Layer & Persistence

*Store changelogs so they're available for review and reference.*

- [ ] **`Changelog` model & migration** — Persist generated changelogs: package name, version change, raw AI response, categorized changes, timestamp
- [ ] **`changelog:history` command** — List past changelogs for a given package
- [ ] **`changelog:review` command** — Review pending/unreviewed changelogs before committing updates

## Phase 5 — MCP Server (Read-Only)

*Let AI agents query updates and changelogs. No write tools — generation is fully automated.*

- [ ] **MCP server foundation** — Laravel-based MCP server using the Model Context Protocol, served via `php artisan mcp:serve`
- [ ] **`list_packages` tool** — Returns all tracked packages with their current versions
- [ ] **`check_updates` tool** — Queries Packagist for newer versions of each tracked package, returns version diff
- [ ] **`get_changelog` tool** — Returns the structured changelog between two versions. If `new_version` is omitted, defaults to the latest available
- [ ] **`get_pending_updates` tool** — Lists packages where a newer version exists on Packagist but no changelog has been generated yet (e.g., still queued)
- [ ] **Agent configuration docs** — Document `.mcp.json` setup for Cursor, Claude Code, GitHub Copilot, and other MCP-compatible assistants

## Phase 6 — Web UI

*Public changelog browsing and authenticated administration.*

- [ ] **Public changelog page** — Publicly accessible route showing changelogs for a specific package. No authentication. Shareable URLs like `/changelogs/laravel/framework`
- [ ] **Public package index** — Lists all tracked packages and links to their changelogs, accessible without login
- [ ] **Admin package management** — Authenticated CRUD for administrators to add, update, and remove tracked packages
- [ ] **Admin dashboard** — Authenticated overview of recent changelogs, generation status, and queue health
- [ ] **Changelog detail enhancements** — Expandable BREAKING / NEW / UPDATED sections and file-path links per change
- [ ] **Search & filter** — Filter changelogs by package name, date range, or severity
- [ ] **Compare view** — Side-by-side comparison of two changelogs for the same package across different versions

## Phase 7 — Notifications

*Proactively inform developers about dependency changes.*

- [ ] **In-app notifications** — Laravel notification bell when new changelogs are available
- [ ] **Email digests** — Daily or weekly email summary of dependency updates and their changelogs
- [ ] **Slack / Discord webhooks** — Post changelog summaries to a channel when updates are detected
- [ ] **PR comments** — Auto-comment on Dependabot or Renovate PRs with the generated changelog

## Phase 8 — Quality & Analysis

*Go deeper than surface-level changelogs.*

- [ ] **Semantic versioning awareness** — Highlight when a change violates semver expectations (e.g., a breaking change in a patch release)
- [ ] **Dependency graph impact** — Show which of your own packages or services are affected by a breaking change in a dependency
- [ ] **Security advisory cross-reference** — Cross-reference changes against known CVEs and security advisories
- [ ] **Changelog confidence score** — Rate how complete a changelog is based on diff coverage and AI confidence
- [ ] **Manual override** — Allow developers to edit, append to, or correct AI-generated changelogs

## Phase 9 — Team & Collaboration

*Make changelogs a shared resource for teams.*

- [ ] **User approval workflow** — Require a team member to review and approve changelogs before updates are applied
- [ ] **Audit trail** — Log who reviewed, approved, or edited each changelog
- [ ] **Shared project configuration** — Team-wide AI provider settings, watched packages, notification preferences
- [ ] **API** — REST or GraphQL API to query changelogs programmatically (for CI pipelines, custom dashboards, etc.)

## Phase 10 — Polish & Performance

*Optimize and harden.*

- [ ] **Caching** — Cache package downloads, diffs, and AI responses to avoid redundant work
- [ ] **Incremental diffing** — Only diff files that actually changed between versions, skip identical files
- [ ] **Parallel processing** — Run diffing and AI analysis for multiple packages concurrently
- [ ] **Large-package handling** — Special handling for monorepos and packages with thousands of files (e.g., `laravel/framework`)
- [ ] **Rate limiting & cost control** — Track AI API usage per provider, set monthly budgets, warn on overuse
- [ ] **Fallback providers** — Configure secondary AI providers if the primary one is unavailable or rate-limited

## Future Ideas

- **`changelog:apply`** — Automatically apply safe (non-breaking) updates based on changelog analysis
- **WordPress plugin/theme support** — Extend beyond Composer to detect WordPress plugin updates and generate changelogs for those too
- **Private package support** — Authenticate with private Packagist / Satis / Git repositories to fetch and diff private packages
- **Changelog format export** — Export changelogs as Markdown, JSON, HTML, or RSS for external consumption
- **Multi-language support** — Generate changelogs in languages other than English based on AI provider capabilities
- **VCS integration** — Automatically create changelog entries as GitHub Releases or Git tags