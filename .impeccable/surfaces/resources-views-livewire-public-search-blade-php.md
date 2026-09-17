---
version: 1
slug: "resources-views-livewire-public-search-blade-php"
primary_target: "resources/views/livewire/public-search.blade.php"
related_targets: ["resources/views/layouts/public.blade.php","app/Livewire/PublicSearch.php"]
---

# Surface brief: public homepage (`/`, PublicSearch) + public layout header/footer

## Scope and mode

- Route `/` (`App\Livewire\PublicSearch`, view `livewire/public-search.blade.php`) and the shared shell `layouts/public.blade.php` (header + footer). Mode: **Persuade** with the search as the single primary action; results are the proof.
- Out of scope this round: `/packages/{vendor}/{name}` and the changelog detail page keep their current content styling (they inherit the new shell only).
- Audience: PHP developer mid-upgrade, no login, wants "what changed, does anything break" in seconds.
- Must remain untouched: search + vendor filter synced to `?q=` / `?vendor=`, 15-per-page pagination, `wire:navigate` links, routes, existing copy ("Find changelogs for Composer packages." / "Search by author or package name to see what changed between versions, before you update."), Pest tests in `tests/Feature/PublicSearchTest.php`.
- Constraint (user-pinned 2026-09-17): build with Flux components; code-led build path.

## Direction contract

THESIS: The homepage is sheet 1 of a technical drawing's revision record. Every package is a numbered row in a revision table with a red delta wherever something breaks. It refuses the centered-hero-plus-card-grid arrangement package search sites ship.

OWN-WORLD: White drawing paper; 1px hairline rules in drawing ink (#14171C). Process blue (#1F5FBF) for links, focus, the active author cell and one title-block accent. Revision red (#C8102E) belongs to the breaking delta alone; nothing else on the page is red. Dark mode is the cyanotype blueprint: Prussian-blue ground (#0B2E5C), pale rules (#9DB6DA at 60%), paper-white text (#E8EEF7). Type: Barlow (engineering gothic) for letterspaced-caps labels and body; JetBrains Mono for package names, versions, counts and dates. Flux input/table/badge/button/tooltip/pagination re-clothed as ruled cells on an 8px unit. No rounded cards, no shadows, no eyebrow above the heading. Newest changelog prints in full ink; rows without a changelog thin to gray.

STORY: The developer lands on a sheet that already reads like the document they wanted. They type the package, the sheet re-counts, they read from→to and whether a red delta sits on the row, hover the delta for the breaking list, and click through to the changelog.

FIRST VIEWPORT (1440×900): A 56px ruled title-block strip: wordmark cell left, Dashboard/Log in cell right. Below, a ruled two-column block: left two thirds, the title cell with the H1 (Barlow 600, 40px) and the one-line subline; right third, three stacked fact cells (PACKAGES n · CHECKED CHANGELOGS n · LATEST REVISION package, version, date), all real counts. Then the FIND PACKAGE cell spanning full width with a 48px mono input, the primary action. Then the AUTHOR strip of toggle cells. Then the revision table header (REV · PACKAGE · FROM → TO · Δ · + · ~ · DESCRIPTION · DATE) with the first rows above the fold. Bottom edge: SHEET n OF m with prev/next.

FORM: Engineering revision-history sheet, candidate 3 of 7 on the ordered list (Patch, Release train, Revision sheet, composer outdated, Bump PR, CHANGELOG.md, Manifest). Seed key a60d7030. Signature interaction: the red Δ in the breaking column opens the breaking-change titles on hover, focus and tap (interactive Flux tooltip). Motion grammar: 150ms ease-out on rule weight, ink and fill; during Livewire loading the table body dims to 50% ("re-inking"); no entrance choreography. Raises carried: REV margin column (orizuru), single alarm colour (chromatophore), newest darkest (suminagashi), identical row instrument with red only past zero (VU bridge), one heading and empty paper left empty (Saville), rules on a fixed unit that re-count rather than stretch (Hoffmann).

FINISH: unreviewed and undocumented is unfinished; this build ends with the finish review, the verdict, DESIGN.md, and every shipping raster carrying its provenance.

## Unresolved

- Whether the package and changelog detail pages get re-clothed in the same sheet (a follow-up round).
- Whether the admin shell adopts Barlow/JetBrains Mono (not this round; fonts are scoped to the public layout).
