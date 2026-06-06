---
name: design-reference-workflow
description: Workflow for aligning all IONOS pages to the immobilienvideos.html design language
metadata:
  type: project
---

# Design Reference Workflow

**Established:** 2026-06-06
**Status:** Active — Gate 2 Design Alignment (Save-Point)  
**Current Target Progress:** `crystals.html` — alignment complete

## Master Design Reference
`immobilienvideos.html` is the single source of truth for the Cinematic Vision Studio premium design language.

## Active Target
`academy.html` — next page to align.

## Next Target
`prompt-generator.html` — queued after academy.html.

## Target Queue (page-by-page)
1. ✅ `scene-editor-test.html` — homepage (alignment complete)
2. ✅ `portfolio.html` — alignment complete
3. ✅ `shop.html` — alignment complete
4. ✅ `crystals.html` — alignment complete (Score 100/100, approved)
5. `academy.html` — next active target
6. `prompt-generator.html`
7. `calendar.html`
8. Legal pages (impressum, datenschutz, agb, cookies, widerruf) — last

## Archiv
- `ki-videos.html` — wird nicht als Standalone aligned. KI-Video-Content wird in `portfolio.html` (Showcase) und `shop.html` (Produkte/Templates/Prompts) integriert.

## Design DNA Rules (from immobilienvideos.html)
- **Colors:** Black `#020205`, Electric Blue `#1872ff` / `#4da0ff`, Cinematic Gold `#e8c355` / `#ffc21f`
- **Typography:** Syne (headings) + DM Sans (body)
- **Nav:** `.cvs-nav-simple` with gradient fade, logo icon + text, gold hover, blur backdrop
- **Buttons:** `.btn-cvs--gold` (premium gold gradient) + `.btn-cvs--ghost` (blue→gold border)
- **Background:** `.cvs-aurora` CSS orbs (4 animated blur gradients)
- **Grain:** `body::after` at `z-index:0`, `opacity:.28`
- **Separators:** `.lightbar` between major sections
- **Footer:** `.cvs-footer-master` with brand + 3 columns + legal links
- **Cursor:** Disabled (`cursor:auto`)
- **Progress:** `#cvs-progress` scroll bar
- **Reveal:** `.reveal` + `.reveal.in` scroll animations
- **Section spacing:** `padding:clamp(84px,13vh,140px) 0`

## Commit Rule
Commit only after a clean visual checkpoint (screenshot or manual browser verify).

## CVS Session Management Rules
1. Nach jeder abgeschlossenen Seite: `git status` + `git diff --stat` + Kurzzusammenfassung
2. Nach jedem Major Milestone: MEMORY.md + CLAUDE.md + TODO.md + workflow-design-reference.md aktualisieren
3. Immer pflegen: Master Reference, Active Target, Next Target, Projekt-Status
4. Session-Ende: SESSION HANDOVER (abgeschlossen, laufend, nächste Aktion, Issues)
5. Context-Monitoring nach jeder Antwort (low / medium / high / critical)
6. Vor neuem Chat: Projekt-State + Workflow-State + Task-State + Design-Reference-State speichern
7. Nie Projekt-Analyse von vorne starten, wenn Memory aktuell ist.

## What NOT to touch during this workflow
- Backend PHP (login, dashboard, profile, API)
- Shop.html (until explicitly queued)
- Impressum placeholders
- MCP / diagnostics

## Related
- [[design-audit]] — previous design gap analysis
- [[cvs-core-css]] — shared stylesheet
