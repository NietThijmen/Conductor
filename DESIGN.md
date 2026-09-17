---
name: Conductor
description: A technical drawing's revision sheet for Composer package changelogs — ruled paper in drawing ink, process blue for action, revision red for the breaking delta alone.
colors:
  board: "#eef1f5"
  paper: "#ffffff"
  drawing-ink: "#14171c"
  drawing-ink-2: "#4a5160"
  drawing-ink-3: "#646b7a"
  rule: "#14171c"
  rule-soft: "color-mix(in oklab, #14171c 16%, transparent)"
  process-blue: "#1f5fbf"
  process-blue-ink: "#174a96"
  process-blue-wash: "#eaf1fb"
  revision-red: "#c8102e"
  revision-red-wash: "#fbe9ec"
  blueprint-board: "#071f40"
  blueprint-paper: "#0b2e5c"
  blueprint-ink: "#eef3fa"
  blueprint-ink-2: "#bfd0e8"
  blueprint-ink-3: "#93abce"
  blueprint-rule: "#dce7f5"
  blueprint-rule-soft: "color-mix(in oklab, #dce7f5 28%, transparent)"
  blueprint-blue: "#9cc3ff"
  blueprint-blue-ink: "#bbd6ff"
  blueprint-blue-wash: "color-mix(in oklab, #9cc3ff 14%, transparent)"
  blueprint-red: "#ff8a97"
  blueprint-red-wash: "color-mix(in oklab, #ff8a97 16%, transparent)"
typography:
  display:
    fontFamily: "Barlow, ui-sans-serif, system-ui, sans-serif"
    fontSize: "2rem"
    fontWeight: 600
    lineHeight: 1.08
    letterSpacing: "-0.015em"
  display-wide:
    fontFamily: "Barlow, ui-sans-serif, system-ui, sans-serif"
    fontSize: "2.5rem"
    fontWeight: 600
    lineHeight: 1.08
    letterSpacing: "-0.015em"
  lede:
    fontFamily: "Barlow, ui-sans-serif, system-ui, sans-serif"
    fontSize: "1.0625rem"
    fontWeight: 400
    lineHeight: 1.5
  figure:
    fontFamily: "JetBrains Mono, ui-monospace, Cascadia Mono, Menlo, Consolas, monospace"
    fontSize: "1.5rem"
    fontWeight: 500
    lineHeight: 1
    fontVariation: "tabular-nums"
  body:
    fontFamily: "Barlow, ui-sans-serif, system-ui, sans-serif"
    fontSize: "0.9375rem"
    fontWeight: 400
    lineHeight: 1.5
  body-small:
    fontFamily: "Barlow, ui-sans-serif, system-ui, sans-serif"
    fontSize: "0.875rem"
    fontWeight: 400
    lineHeight: 1.45
  mono:
    fontFamily: "JetBrains Mono, ui-monospace, Cascadia Mono, Menlo, Consolas, monospace"
    fontSize: "0.875rem"
    fontWeight: 400
    fontVariation: "tabular-nums"
  mono-package:
    fontFamily: "JetBrains Mono, ui-monospace, Cascadia Mono, Menlo, Consolas, monospace"
    fontSize: "0.9375rem"
    fontWeight: 400
  label:
    fontFamily: "Barlow, ui-sans-serif, system-ui, sans-serif"
    fontSize: "0.6875rem"
    fontWeight: 600
    letterSpacing: "0.14em"
  wordmark:
    fontFamily: "Barlow, ui-sans-serif, system-ui, sans-serif"
    fontSize: "0.875rem"
    fontWeight: 600
    letterSpacing: "0.12em"
  pager-label:
    fontFamily: "JetBrains Mono, ui-monospace, Cascadia Mono, Menlo, Consolas, monospace"
    fontSize: "0.75rem"
    fontWeight: 400
    letterSpacing: "0.08em"
    fontVariation: "tabular-nums"
  footnote:
    fontFamily: "Barlow, ui-sans-serif, system-ui, sans-serif"
    fontSize: "0.75rem"
    fontWeight: 400
rounded:
  none: "0"
spacing:
  unit-0-75: "6px"
  unit: "8px"
  unit-1-25: "10px"
  unit-1-5: "12px"
  unit-2: "16px"
  unit-3: "24px"
  unit-4: "32px"
  unit-5: "40px"
  unit-6: "48px"
  unit-7: "56px"
components:
  frame:
    backgroundColor: "{colors.paper}"
    textColor: "{colors.drawing-ink}"
    rounded: "{rounded.none}"
    width: "76rem"
  titleblock:
    backgroundColor: "{colors.paper}"
    textColor: "{colors.drawing-ink}"
    height: "{spacing.unit-7}"
    padding: "0 {spacing.unit-3}"
  titleblock-brand-hover:
    backgroundColor: "{colors.process-blue-wash}"
    textColor: "{colors.process-blue-ink}"
  label:
    textColor: "{colors.drawing-ink-2}"
    typography: "{typography.label}"
  fact-cell:
    backgroundColor: "{colors.paper}"
    textColor: "{colors.drawing-ink}"
    typography: "{typography.figure}"
    padding: "{spacing.unit-2} {spacing.unit-3}"
  search-input:
    backgroundColor: "{colors.paper}"
    textColor: "{colors.drawing-ink}"
    typography: "{typography.mono}"
    rounded: "{rounded.none}"
    height: "{spacing.unit-6}"
  search-input-focus:
    backgroundColor: "{colors.paper}"
    textColor: "{colors.drawing-ink}"
  author-cell:
    backgroundColor: "transparent"
    textColor: "{colors.drawing-ink-2}"
    rounded: "{rounded.none}"
    padding: "{spacing.unit-1-25} {spacing.unit-2}"
    height: "{spacing.unit-5}"
  author-cell-hover:
    backgroundColor: "{colors.process-blue-wash}"
    textColor: "{colors.process-blue-ink}"
  author-cell-active:
    backgroundColor: "{colors.process-blue}"
    textColor: "{colors.paper}"
  table-header-cell:
    textColor: "{colors.drawing-ink-2}"
    typography: "{typography.label}"
    padding: "{spacing.unit-1-25} {spacing.unit-1-5}"
  table-cell:
    textColor: "{colors.drawing-ink}"
    typography: "{typography.body}"
    padding: "{spacing.unit-2} {spacing.unit-1-5}"
  table-row-hover:
    backgroundColor: "{colors.process-blue-wash}"
  table-row-thin:
    textColor: "{colors.drawing-ink-3}"
  delta:
    backgroundColor: "{colors.revision-red-wash}"
    textColor: "{colors.revision-red}"
    typography: "{typography.mono}"
    rounded: "{rounded.none}"
    padding: "2px 8px 2px 6px"
  delta-hover:
    backgroundColor: "{colors.revision-red}"
    textColor: "{colors.paper}"
  breaking-note:
    backgroundColor: "{colors.paper}"
    textColor: "{colors.drawing-ink}"
    rounded: "{rounded.none}"
    padding: "{spacing.unit-1-5} {spacing.unit-2}"
    width: "22rem"
  newest-tag:
    textColor: "{colors.process-blue-ink}"
    rounded: "{rounded.none}"
    padding: "1px 5px"
  nav-button:
    backgroundColor: "transparent"
    textColor: "{colors.drawing-ink}"
    rounded: "{rounded.none}"
  nav-button-hover:
    backgroundColor: "{colors.process-blue-wash}"
    textColor: "{colors.process-blue-ink}"
  nav-button-disabled:
    textColor: "{colors.drawing-ink-3}"
  footer:
    textColor: "{colors.drawing-ink-2}"
    typography: "{typography.footnote}"
    padding: "{spacing.unit-1-5} {spacing.unit-3}"
---

# Design System: Conductor

## Overview

**Creative North Star: "The Revision Sheet"**

The public surface is sheet 1 of a technical drawing's revision record. A white sheet of drawing paper sits on a cool grey board, framed by a single 1px rule in drawing ink, and everything on it is a ruled cell: the title block across the top, a title cell with three stacked fact cells beside it, a FIND PACKAGE cell, a strip of author toggle cells, and a revision table whose rows are numbered in a margin column. Nothing floats. There are no cards, no shadows, no radius; depth is drawn, never lit. Dark mode is the same sheet printed as a cyanotype blueprint: Prussian-blue paper, pale rules, paper-white ink.

Two typefaces do all the work. Barlow, an engineering gothic, sets the heading, prose, and every letterspaced-caps label. JetBrains Mono sets whatever is data: package names, versions, counts, dates, the input text, and the page counter. Colour is rationed by meaning: process blue is the only action colour (links, focus, hover wash, the pressed author cell, the NEWEST tag); revision red belongs to breaking changes alone and appears nowhere else on the sheet. Rows without a checked changelog thin to grey; the newest changelog prints in full ink.

This world currently governs the public layout (`/`, and the shared shell of the package and changelog detail pages). The admin app is a separate, stock Flux surface and is not described here.

**Key Characteristics:**
- Ruled cells on an 8px unit; 1px hairlines in ink are the only structure.
- Flat paper: zero radius, zero shadow, white-on-grey-board framing.
- Barlow for words and labels, JetBrains Mono for data.
- One action colour (process blue), one alarm colour (revision red, breaking only).
- Cells re-count rather than stretch: the table collapses into stacked rows below 64rem.
- Motion is 150ms ease-out on fill and ink; the table body dims to 50% while re-inking.

## Colors

Drawing ink on white paper, with a cool grey board behind the sheet; a process-blue pen for anything you can act on and a single revision-red pen reserved for breaking changes.

### Primary
- **Process Blue** (`process-blue`): the pen for action. Focus outline (2px, 2px offset), text selection (28% wash), input caret, the pressed author cell fill, and the Flux accent inside the sheet.
- **Process Blue Ink** (`process-blue-ink`): the darker blue for hover text on links, package names, description titles, the brand cell, ghost buttons, and the NEWEST tag border/text.
- **Process Blue Wash** (`process-blue-wash`): the pale fill on hovered rows, author cells, ghost buttons and the brand cell.

### Secondary
- **Revision Red** (`revision-red`): the breaking delta only: the delta button's ink and border, the delta column header, the legend's delta, and the breaking-note title. Hover and open flip the button to solid red with paper text.
- **Revision Red Wash** (`revision-red-wash`): the delta button's fill at rest.

### Neutral
- **Board** (`board`): the desk behind the sheet, visible only at 64rem and up where the frame gets a 40px/32px margin.
- **Paper** (`paper`): the sheet itself, the input, and the breaking-note surface.
- **Drawing Ink** (`drawing-ink`): primary text, the heading, cell values, package names, and every structural rule (`rule` is the same value).
- **Drawing Ink 2** (`drawing-ink-2`): the lede, labels, table headers, the vendor half of a package name, summaries, the legend, footer text and author-cell text at rest.
- **Drawing Ink 3** (`drawing-ink-3`): placeholder text, zero counts, em-dashes, the arrow glyph, "thin" rows without a changelog, and disabled nav buttons.
- **Rule Soft** (`rule-soft`): 16% ink for the hairlines between table rows, between author cells, and under the breaking-note title.

### Blueprint (dark) counterparts
Every token above has a `blueprint-*` twin, swapped on `.dark .sheet`. Blueprint paper is Prussian blue over a darker board; ink becomes paper-white; rules go pale blue-white with a 28% soft variant; process blue and revision red brighten to pastel so they still read as ink on the dark ground. Washes become transparent mixes (14% blue, 16% red) so they tint the blueprint instead of covering it. The pressed author cell on the blueprint uses `blueprint-board` as its text so the fill stays legible.

### Named Rules
**The Single Alarm Rule.** Revision red marks breaking changes and nothing else. A zero in the delta column is grey, not red; the delta button is the only red fill on the sheet.

**The One Pen Rule.** Process blue is the only colour that signals interaction. Hover is always a blue wash with blue-ink text; pressed is solid blue with paper text; focus is a 2px blue outline offset 2px. Nothing else changes colour on hover.

**The Ink Is The Rule Rule.** Structural rules are drawn in full drawing ink, not a lighter grey. Only the hairlines inside a block (between rows, between author cells) drop to the 16% soft rule.

## Typography

**Display Font:** Barlow (with ui-sans-serif, system-ui, sans-serif)
**Body Font:** Barlow (with ui-sans-serif, system-ui, sans-serif)
**Label/Mono Font:** JetBrains Mono (with ui-monospace, Cascadia Mono, Menlo, Consolas, monospace)

Both are served by the Laravel Vite Bunny fonts plugin at 400/500/600 and reach the sheet through `--font-barlow` and `--font-jetbrains-mono`.

**Character:** An engineering gothic for the words and a monospace for the data. Barlow is condensed enough to letterspace into stencil-like caps labels and open enough at 600 to carry a 40px heading without shouting; JetBrains Mono, always with tabular figures, makes package names, versions, counts and dates read as measured values rather than prose.

### Hierarchy
- **Display** (600, 2rem rising to 2.5rem at 48rem and up, 1.08, -0.015em): the single H1 in the title cell, balanced and capped at 18ch. One heading per sheet.
- **Lede** (400, 1.0625rem, 1.5; 1rem at 64rem and up): the one-line subline under the heading, capped at 52ch below 64rem, `text-wrap: pretty` above.
- **Figure** (JetBrains Mono 500, 1.5rem, 1, tabular): the counts in the fact cells. Text-valued facts (LATEST REVISION) drop to mono 0.875rem/1.4.
- **Body** (400, 0.9375rem): table cell text and the description title (which is 500).
- **Body Small** (400, 0.875rem, 1.45): summaries (clamped to 2 lines, 62ch), the breaking note (0.8125rem), the title-block field and author cells (0.8125rem, 500).
- **Mono** (JetBrains Mono 400, 0.875rem, tabular): revision numbers, versions, counts, dates, the input text (1rem). Package names are mono 0.9375rem with the vendor half in ink-2 and the name half at 600. The `to` version is 600; the `from` version is 400.
- **Label** (600, 0.6875rem, 0.14em, uppercase): every cell label (PACKAGES, FIND PACKAGE, AUTHOR, REVISION RECORD), table column headers, footer wordmark, and the NEWEST tag (0.625rem). Nav buttons use the same at 0.12em; the wordmark is 0.875rem at 0.12em.
- **Pager Label** (JetBrains Mono 400, 0.75rem, 0.08em, uppercase, tabular): SHEET n OF m.

### Named Rules
**The Data Is Mono Rule.** Anything that is a value (package name, version, count, date, the search text, the sheet counter) is set in JetBrains Mono with tabular figures. Anything that is a sentence is Barlow.

**The Label Is A Cell Heading Rule.** Letterspaced caps labels name the cell they sit in (as a `<dt>`, `<th>`, `<label>` or the leading cell of a strip). They never sit above the H1 as a kicker.

**The Newest Darkest Rule.** The row with the newest checked changelog prints in full ink with a NEWEST tag; rows without any checked changelog thin to ink-3 across every cell.

## Layout

The sheet is a single column of full-width ruled blocks inside a frame. The frame is `max-width: 76rem`, centred, paper-white, with 1px ink rules on its inline edges; at 64rem and up the board shows around it (`padding: 40px 32px`) and the frame gains a full 1px border and a `min-height` of the viewport minus that margin. Below 64rem the frame is edge to edge with only the side rules.

Every measurement is a multiple of `--sheet-unit` (8px). Cells pad 24px/16px at the narrowest width and 24px from 40rem up; the title cell pads 40px top / 32px bottom, rising to 56px / 48px at 64rem and up. Strips (author, table head, pager, footer) pad 12px vertically. The title block is exactly 56px tall.

Blocks stack vertically and are separated by a 1px ink rule (`border-bottom`), with the last block in a page dropping its rule so it meets the footer's `border-top` cleanly. Inside a block, cells divide with `border-left`/`border-right`/`border-top` rules rather than gaps: the title block is `auto 1fr auto`, the title section is `2fr 1fr` at 64rem and up, the facts are a 2-column grid (third fact spanning) below 64rem and a stacked column beside the title above it, the author strip is `auto 1fr`, and the footer is `auto 1fr`.

Breakpoints, as used: 40rem (cell padding widens to 24px), 48rem (the DRAWN FROM field appears in the title block; the H1 grows to 40px), 64rem (board margin, framed sheet, two-column title, side-by-side facts, the table renders as a real table), and 64–80rem (a narrow-desktop band where the table switches to `table-layout: auto`, fixed column widths release, the numeric and date columns refuse to wrap, package keeps a 14rem minimum and can only break after the vendor slash, description keeps a 12rem minimum).

Below 64rem the table re-counts into stacked rows: the header row is visually hidden, each row becomes a four-column grid laid out as `rev pkg pkg date / . ver ver ver / . delta new updated / . desc desc desc`, with the REV number holding the margin column, counts prefixed by their symbol via `data-label`, and 16px padding per row. Rows keep the soft hairline between them and the blue wash on hover.

Fixed table column widths at 80rem and up: REV 4rem, PACKAGE 16rem, FROM → TO 12rem, each count 4rem, DATE 8.5rem; DESCRIPTION takes the remainder. Numeric cells right-align with a 2ch minimum.

## Elevation & Depth

There are no shadows. `box-shadow: none` is set explicitly on the input and the breaking note. Depth is drawn: the paper sits on the board by contrast alone, and every layer inside the sheet is a ruled cell. The breaking note (the tooltip that opens from a red delta) is the only element that overlays the sheet; it is paper with a 1px ink border and a 4px paper-coloured outline, which reads as a note pinned over the drawing rather than a floating card. Livewire loading dims the table body to 50% opacity ("re-inking") instead of overlaying a spinner.

### Named Rules
**The Drawn Not Lit Rule.** No shadow, glow, blur or gradient on any surface. If a layer must sit above the sheet, give it a paper fill, a 1px ink border and a paper outline.

## Shapes

Zero radius everywhere: `border-radius: 0` is set on the input, the ghost buttons and the breaking note to override Flux defaults. Every surface is a rectangle bounded by 1px rules; hairlines are the whole form language. The two authored glyphs are triangles drawn in stroke, not fill: the sheet mark (24-viewbox triangle with a horizontal bar, 1.75 stroke, round joins) and the delta (12-viewbox triangle, 1.5 stroke, rendered 10px inside the delta button). Tags (NEWEST) are a 1px `currentColor` border around 0.625rem caps with 1px/5px padding. The delta button is a 1px red-bordered rectangle with 2px/8px/2px/6px padding.

## Components

### Buttons
Ghost buttons re-clothed as ruled-cell text; nothing filled except the pressed author cell.
- **Shape:** square (0 radius).
- **Nav / ghost** (`nav-button`): Flux ghost button, Barlow 600 0.6875rem uppercase 0.12em, ink text, transparent. Used for Log in / Dashboard (with a trailing arrow), Clear filters, Prev / Next (with chevrons).
- **Hover:** process-blue wash fill, blue-ink text, 150ms ease-out.
- **Disabled:** ink-3 text at full opacity (no fade).
- **Focus:** 2px process-blue outline, 2px offset (sheet-wide).

### Chips
The author strip: toggle cells, not pills.
- **Style** (`author-cell`): inline-flex, baseline-aligned, 40px minimum height, 10px/16px padding, Barlow 500 0.8125rem in ink-2, transparent fill, a soft 16% rule on the right edge separating cells; the count beside the name is mono 0.75rem tabular.
- **State:** hover is blue wash + blue-ink text; `aria-pressed="true"` is solid process blue with paper text (board text on the blueprint). The strip wraps onto further lines as authors grow.

### Cards / Containers
There are no cards. Containers are ruled cells.
- **Corner Style:** none.
- **Background:** paper. Board only behind the frame.
- **Shadow Strategy:** none (see Elevation & Depth).
- **Border:** 1px ink rules on block bottoms and cell sides; 16% soft rule inside blocks.
- **Internal Padding:** 24px (16px inline below 40rem); strips 12px vertical.

### Inputs / Fields
- **Style** (`search-input`): Flux input with a magnifying-glass leading icon and clearable trailing button, 48px tall, JetBrains Mono 1rem in ink on paper, 1px ink border, 0 radius, no shadow; placeholder in ink-3 at full opacity, icon in ink-3. Its label is a `label`-style caps heading above it inside a 16px-padded search cell.
- **Focus:** border turns process blue and the Flux ring becomes a 30% process-blue ring with no offset, 150ms ease-out on the border.
- **Error / Disabled:** not present on this surface.

### Navigation
- **Title block:** a 56px grid of `brand | field | account`. The brand cell (mark + wordmark, 12px gap, right rule) hovers to blue wash; the middle DRAWN FROM field (label + sentence in ink-2, 0.8125rem) appears at 48rem and up; the account cell holds one ghost nav button behind a left rule. Below 48rem the grid drops to two columns and the left rule of the account cell is removed.
- **Pager:** SHEET n OF m in mono caps between Prev and Next ghost buttons, right-aligned in the sheet-nav strip opposite a delta / + / ~ legend.
- **Footer:** `wordmark | sentence` grid with a top rule and a rule between the two cells, 0.75rem in ink-2.

### Revision Table (signature)
A Flux table re-clothed as a ruled revision record. Column headers are `label` caps in ink-2 with a 1px ink rule beneath; the Δ / + / ~ headers are mono `<abbr>` glyphs with the Δ in revision red. Rows are separated by the 16% soft rule, cells pad 16px/12px (24px at the outer edges), text is Barlow 0.9375rem top-aligned, and hovering a row washes every cell in process blue. The REV column prints a zero-padded two-digit margin number. Package names are mono with the vendor in ink-2 and the name at 600; FROM → TO sets the arrow in ink-3 and the destination version at 600. Counts are mono, right-aligned, and zero or missing values are ink-3. Rows without a checked changelog carry `sheet-tr--thin` and go ink-3 throughout. The newest changelog on the sheet gets a NEWEST tag under its date.

### Breaking Delta and Note (signature)
The red Δ button (`delta`): mono 600 0.875rem count with the stroked-triangle glyph, revision-red text and border on a red wash, flipping to solid red with paper text on hover or when expanded. It is rendered twice: a hover/focus-triggered interactive Flux tooltip for fine pointers and a tap-toggled one for touch, switched by `@media (hover: none)`. The note (`breaking-note`): paper, 1px ink border, 4px paper outline, 22rem max (viewport minus 2rem on small screens), 12px/16px padding, Barlow 0.8125rem/1.45; a mono 600 0.75rem red title ("Breaking in vendor/name x.y.z") over a soft hairline, a decimal list of up to five breaking titles with mono ink-3 markers, an ink-3 "+ n more" line, and a blue-ink underlined "See every change" link (4px underline offset) after 10px.

### Fact Cells
The title section's right-hand `<dl>`: each fact is a label over a `figure` count (mono 500 1.5rem tabular) with 6px gap, 16px/24px padding, divided by ink rules; the LATEST REVISION fact sets a mono 0.875rem package + version link (underlines blue-ink on hover) over an ink-3 date.

### Empty Sheet
When no row matches, the table area shows a left-aligned 48px-padded cell: a 1.125rem/500 ink title, a 52ch ink-2 hint and, when filters are active, a Clear filters ghost button. The paper is otherwise left empty.

## Do's and Don'ts

### Do:
- **Do** build every new public block as a ruled cell: 1px drawing-ink `border-bottom` between blocks, side rules between cells, padding in multiples of 8px.
- **Do** set every value (name, version, count, date, counter) in JetBrains Mono with `font-variant-numeric: tabular-nums`, and every sentence in Barlow.
- **Do** use process blue for every interactive state: blue wash + blue-ink text on hover, solid blue + paper text when pressed, a 2px blue outline offset 2px on focus, all at 150ms ease-out.
- **Do** keep revision red for breaking changes only, and only past zero: a zero count is ink-3.
- **Do** thin a row to ink-3 when it has no checked changelog, and mark the single newest changelog with the NEWEST tag.
- **Do** dim a re-loading region to 50% opacity rather than covering it with a spinner.
- **Do** swap to the `blueprint-*` tokens under `.dark .sheet` rather than authoring separate dark colours.
- **Do** re-count the layout at 64rem (stacked rows) and 40rem/48rem (padding, field, H1 size) rather than letting cells stretch.

### Don't:
- **Don't** add border-radius, box-shadow, gradients or blur to any surface inside the sheet; Flux defaults for these are overridden to 0 / none.
- **Don't** use red anywhere except the breaking delta, its column header, its legend entry and the breaking-note title.
- **Don't** introduce a second interaction colour; green/indigo semantics from other pages are not part of this sheet.
- **Don't** put a caps label above the H1 as an eyebrow or kicker; labels name cells, and the sheet has one heading.
- **Don't** wrap content in cards or a centred hero; the sheet is one column of full-width ruled blocks.
- **Don't** use Instrument Sans or a system display face on the public sheet; Barlow and JetBrains Mono are loaded for it.
- **Don't** fade disabled controls with opacity; disabled is ink-3 text at full opacity.
