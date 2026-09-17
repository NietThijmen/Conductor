# Product

<!-- impeccable:product-schema 1 -->

## Platform

web

## Users

- **PHP developers about to run `composer update`.** They land on the public site (no login) to find a package and read what actually changed between the version they run and the one they are about to install. They are in the middle of a dependency upgrade, often on a laptop in an editor/terminal context, and want the answer in seconds.
- **The maintainer/admin of this instance.** Logs in to manage registries, the tracked-package watchlist, and to review changelogs moving through backlog → checking → checked.
- **AI coding agents** (Cursor, Claude Code, Copilot) querying the same changelogs through the MCP server and JSON API. Confirmed as a first-class audience in OVERVIEW.md.

## Product Purpose

Composer Changelogger (app name: "Changelogger") gives developers peace of mind when updating dependencies. For every version transition of a tracked Composer package it stores a human-readable, AI-generated changelog broken into **BREAKING**, **NEW** and **UPDATED** changes, plus a title and summary. Success: a developer knows what will break before they commit or deploy, without scouring release notes or diffing tags by hand.

## Positioning

The changelog is generated from the **actual file-system diff between the two package versions**, not from the maintainer's release notes. It therefore surfaces what changed in the code even when upstream wrote nothing, and it exists for every transition in the chain (1.0.0 → 1.0.1 → 1.1.0), not only the tagged release the maintainer chose to describe. Packagist, GitHub Releases and Dependabot summaries cannot truthfully claim this.

## Operating Context

- `composer update` / `composer outdated` workflows; Packagist and private registries (Private Packagist, Satis, WPackagist) with none/basic/token/composer auth.
- An hourly scheduler polls registries for tracked packages and dispatches a queued `GenerateChangelog` job per missing version transition; the job downloads both versions, diffs them, runs an AI agent (Laravel AI SDK, provider-agnostic) and persists the structured result.
- Changelog lifecycle: `backlog` (queued) → `checking` (generating) → `checked` (published). Only `checked` changelogs appear publicly.
- Public routes: `/` (search packages and vendors), `/packages/{vendor}/{name}` (a package's checked changelogs), `/packages/{vendor}/{name}/{new_version}` (one changelog). Admin routes behind login: dashboard, registries, packages, changelogs (kanban).
- MCP clients and the REST API read the same data; PR-comment, Slack and email notifications are roadmap items, not shipped.

## Capabilities and Constraints

- Stack: Laravel 13, Livewire 4, Flux + Flux Pro 2.x, Tailwind 4, Vite 8, Pest, PHPStan level 7. SQLite locally.
- Data on hand per package: `vendor/name`, current tracked version, registry, active flag, checked changelogs each with `old_version → new_version`, optional `title`, optional `summary`, and typed changes (`breaking` / `new` / `updated`, each with `title` and optional `message`).
- Search is a `LIKE` filter on the package name, plus a vendor filter; both sync to the URL (`?q=`, `?vendor=`). Results paginate at 15.
- Terminology: **package** (`vendor/name`), **vendor** (the part before the slash; the public UI calls it "author"), **registry**, **changelog** (one version transition), **change** (one typed entry), **breaking / new / updated**.
- The public UI must be built with Flux components (user-pinned, 2026-09-17).
- Public pages need no auth; a logged-in user sees a "Dashboard" link in the public header.
- Undecided: whether the public site will ever list inactive packages; whether a package without any checked changelog should be searchable (today it is listed with "No published changelog yet").

## Brand Commitments

- Name: "Changelogger" in the UI (`config('app.name')`), "Composer Changelogger" in docs.
- The current logo mark (`resources/views/components/app-logo-icon.blade.php`) is the Laravel starter kit's placeholder glyph, not an owned asset; nothing binds it.
- No confirmed voice guide. Existing copy is plain, direct, second person ("before you update").
- Semantic vocabulary already used across public pages: breaking = red, new = green, updated = indigo. Not pinned as a palette, but the three-way categorisation itself is product truth and must stay legible.

## Evidence on Hand

- Real product data model and pipeline (see Operating Context). Local data is `MockDataSeeder` output: Packagist + WPackagist registries, `laravel/laravel` and `laravel/framework`, 5 checked changelogs with Faker titles/summaries and 50 Faker changes on the first. Treat all seeded text as synthetic.
- No customers, testimonials, usage numbers, benchmarks or press. Do not fabricate any.
- No product screenshots or illustrations exist.

## Product Principles

1. **Show what changed, not what was claimed.** The diff is the source of truth; the UI should feel like reading the code's own account.
2. **Breaking first.** A developer scanning for risk must find breaking changes before anything else, on every surface.
3. **Answer in seconds.** Search → package → changelog with no login, no onboarding, no marketing detour.
4. **Versions are the unit.** Every changelog is a `from → to` pair; the UI should make the transition, not the release, the thing you read.
5. **Machine-readable is a feature.** The same data serves humans, the API and MCP agents; nothing on the web should contradict what an agent gets.
