---
name: design-audit-2026-05-28
description: Globaler Design-Audit aller öffentlichen Seiten gegen scene-editor-test.html (Master)
metadata:
  type: project
---

# Design-Audit — Cinematic Vision Studio
**Datum:** 2026-05-28 | **Agent:** Claude Code | **Master-Referenz:** `scene-editor-test.html`

---

## Master-Design-DNA (scene-editor-test.html)

| Token | Wert | Verwendung |
|---|---|---|
| `--black` | `#020205` | Body-Hintergrund |
| `--blue-bright` | `#1872ff` | Glow, Links, Akzente |
| `--gold-warm` | `#d4a93c` | Primäres Gold |
| `--gold-bright` | `#e8c355` | Sekundäres Gold, Highlights |
| `--white` | `#edf2ff` | Primärer Text |
| Body Font | `'DM Sans', sans-serif` | Fließtext |
| Heading Font | `'Syne', sans-serif` | Überschriften, CTAs |
| Film Grain | `body::before` SVG Noise | Cinematic Textur |
| Glass Panels | `backdrop-filter: blur(22px)` | Cards, Modals, Nav |
| Glow System | `box-shadow` + Radial-Gradient | Hover, Fokus, Akzente |
| Section Labels | Gold-Linie + `.section-label` | Sektions-Einleitung |
| Buttons | `.btn-primary` (Blue Glow), `.btn-secondary` (Gold Border) | CTAs |

---

## Design-Gap-Tabelle

| Seite | Grain | Master-Farben | Fonts | Glass | Hero | Glow | Section Labels | CTAs |
|---|---|---|---|---|---|---|---|---|
| **scene-editor-test** | ✅ | ✅ | ✅ | ✅ | ✅ (100vh Video) | ✅ | ✅ | ✅ |
| **academy** | ❌ | 🟡 (eigene) | ✅ (Overrides) | ❌ | 🟡 (einfach) | 🟡 (Border only) | ❌ | 🟡 (blau) |
| **prompt-generator** | ✅ (neu) | ✅ (neu) | ✅ (neu) | ❌ | 🟡 (einfach) | ❌ | ❌ | 🟡 (gold) |
| **portfolio** | ❌ | 🟡 (eigene) | ✅ (Overrides) | ❌ | 🟡 (einfach) | 🟡 (Frame only) | ❌ | 🟡 |
| **shop** | ❌ | 🟡 (eigene) | ✅ (Overrides) | ❌ | ❌ (Coming Soon) | ❌ | ❌ | 🟡 |
| **crystals** | ❌ | 🟡 (eigene) | ✅ (Overrides) | 🟡 (nur Banner) | 🟡 (einfach) | 🟡 (Pack hover) | ❌ | 🟡 |
| **calendar** | ❌ | ✅ | ✅ | ✅ | 🟡 (gut) | 🟡 (Cal glow) | ❌ | ✅ |
| **ki-videos** | ❌ | 🟡 (eigene) | ❌ (Segoe) | ❌ | ❌ (Coming Soon) | ❌ | ❌ | 🟡 |

---

## Priorisierte Fix-Liste (kleinste sichere Schritte)

### Batch 1 — Schnellste Premium-Wins (Session 9 fortsetzen)
1. **Film Grain** auf `academy.html`, `portfolio.html`, `shop.html`, `crystals.html`, `calendar.html`, `ki-videos.html`
2. **Font-Fix** auf `ki-videos.html` (`Segoe UI` → `DM Sans`)
3. **Gold-Farbton** angleichen auf `academy.html`, `portfolio.html`, `shop.html`, `crystals.html`, `ki-videos.html` (`#f5c542` → `#d4a93c`)

### Batch 2 — Glass-Panel Aufwertung (Session 10)
4. **Academy Cards + Modal** mit `backdrop-filter`, Glass-Border, Glow-Hover
5. **Prompt Generator** `gen-card` mit Glass-Panel + Glow-Shadows
6. **Portfolio** Cards mit Glass-Panel statt Gradient-Hintergrund

### Batch 3 — Content & Flow (Session 11)
7. **Shop + KI-Videos** mit echtem Content (Beispiel-Assets, Featured Prompts, Demo-Videos)
8. **Cross-Links** zwischen Academy ↔ Prompt Generator ↔ Studio verstärken
9. **Landing Page** CTA zu Academy / Prompt Generator hinzufügen

---

## Phase K — Prompt Generator Masterpiece Plan

| Bereich | Status | Kleinste Verbesserung |
|---|---|---|
| Hero | 🟡 Einfach | Kein 100vh nötig, aber stärkerer visueller Einstieg |
| Formular | 🟡 Generisch | `gen-card` mit Glass-Panel + Glow-Shadows |
| Inputs | 🟡 Standard | Focus-State mit blauem Glow statt nur gold border |
| Output | 🟡 Einfach | Syntax-Highlighting der Prompt-Teile (Genre, Kamera, Licht) |
| Negative Prompt | ✅ Erledigt | Preset-Select implementiert |
| Objektiv | ✅ Erledigt | 24/50/85/135mm Select implementiert |
| Academy-Link | ✅ Erledigt | Link unter Output |
| Mobile UX | ✅ Gut | Grid bricht sauber um |

---

## Phase L — Academy Masterpiece Plan

| Bereich | Status | Kleinste Verbesserung |
|---|---|---|
| Filter-System | ✅ Gut | `aria-pressed` hinzugefügt |
| Card-Design | 🟡 Funktional | Glass-Panel + Glow-Hover |
| Modal | 🟡 Gut | Glass-Panel + stärkere Glow-Borders |
| Mobile UX | ✅ Gut | Responsive, Modal full-width |
| CTA | ✅ Gut | Kontextuelle CTAs pro Guide |
| Premium-Wirkung | 🟡 Mittel | Film Grain fehlt, kein Glow-System |

**Fehlende Guides (hoher Mehrwert, niedrige technische Gefahr):**
- Bewegung in KI-Videos — was funktioniert, was nicht?
- Sounddesign für KI-Videos
- Kamera-Bewegung — Dolly, Crane, Tracking, Steadicam
- Charakter-Konsistenz über mehrere Szenen
- Produkt-Fotografie mit KI
- Batch-Generierung — Effizienz-Workflows
- TikTok Algorithmus — was der Algorithmus bevorzugt

---

## Phase M — Portfolio / Shop / KI-Videos Audit

| Seite | Stärke | Schwäche |
|---|---|---|
| **portfolio** | Szenen-Gradienten sind cinematic | Kein Grain, keine Glass-Panels, nur 3 Items |
| **shop** | Struktur vorhanden | Nur "Coming Soon", kein Mehrwert |
| **ki-videos** | Struktur vorhanden | Nur "Coming Soon", kein Mehrwert |
| **crystals** | Pläne gut strukturiert, FAQ nützlich | Kein Grain, Coming-Soon-Badges überall |
| **calendar** | Am nächsten am Master! | Kein Grain, Footer unterscheidet sich |

---

## Phase N — Global UX / Creator Flow Audit

### User Journey
1. **Landing** (scene-editor-test) → Stärkste Seite, aber CTA zu Academy/Prompts fehlt
2. **Academy** → Gut, aber visueller Bruch zur Landing
3. **Prompt Generator** → Funktional, aber generisches Design
4. **Ergebnis** → Prompt kopiert → aber was dann? Keine klare "Nächster Schritt"-Guidance
5. **Shop / Booking / Studio** → Shop kommt zu spät im Flow

### Schnellste UX-Wins
1. **Cross-Links** — Academy-Guides verlinken stärker auf Prompt Generator
2. **Prompt Generator Output** — "Im Studio verwenden" Button prominenter
3. **Shop + KI-Videos** — Mit echtem Content füllen (Beispiel-Prompts, Demo-Videos)
4. **Landing Page** — Sekundärer CTA zu Academy / Prompt Generator

---

## Was wurde in dieser Session erledigt (2026-05-28)

| Datei | Änderung |
|---|---|
| `academy.html` | Body scroll-lock, Modal footer full-width mobile, Filter `aria-pressed` |
| `prompt-generator.html` | Negative-Prompt-Select, Objektiv-Select, 3 Licht-Optionen, Academy-Link, **Film Grain**, **DM Sans Font**, **Gold-Ton `#d4a93c`** |
| `memory/MEMORY.md` | Prioritäten aktualisiert |
| `memory/design-audit.md` | Diese Datei erstellt |
