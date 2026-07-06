# COMPONENT_LIBRARY.md — Cinematic Vision Studio
# Komponentenkatalog (Klassen, Zustände, Verwendung)

| Feld | Wert |
|---|---|
| Version | 1.0 (Architecture Lock, 2026-07-05) |
| Basis | Real existierende Klassen der Referenzseiten (Ist) + geplante Plattform-Komponenten (Ziel) |
| Status-Legende | ✅ vorhanden & gelockt · 🔁 vorhanden, wird konsolidiert · 🆕 geplant |

> Regel: Neue UI wird aus DIESEM Katalog gebaut. Fehlt eine Komponente, wird sie HIER
> spezifiziert (Name, Klassen, Zustände), dann implementiert. Keine Einweg-Klassen.

---

## 1. Navigation ✅

**`.cvs-nav-simple`** — globale Top-Nav (fixiert, Gradient-Fade, Blur, Gold-Hover)

| Teil | Klasse | Verhalten |
|---|---|---|
| Logo-Link | `.nav-logo` → `.cvs-nav-img` + `.cvs-nav-txt` (`.nav-t1/.nav-t2` Wortmarke, `.nav-t3` Sub) | führt zu `/` |
| Linkleiste | `.nav-links` > `.nav-link` | Hover: Gold-Unterstreichung; aktiv: `.active` |
| Aktionen | `.nav-actions` > `.nav-btn-ghost` (Login) + `.nav-btn-gold` (Studio-CTA) | eingeloggt: Ghost → Avatar-Menü 🆕 |
| Kristall-Pill | `.wallet-pill` (heute nur Studio) | 🆕 wandert in die globale Nav (eingeloggt), klick → `/kristalle` |
| Burger | `.nav-burger` (3 Spans) | < 900px sichtbar, öffnet `.mob-nav` |
| Mobile-Menü | `.mob-nav` > `.mob-link`, `.mob-cta` | Vollbild-Overlay, CTA zuoberst, Body-Scroll-Lock |

Logo-Quelle: **`assets/cvs-logo-icon.png` in der Nav** (verbindlich); Wort-Bild-Marke
`cvs-logo.png` nur Footer/OG. (Optimierung auf WebP: Cleanup-Phase.)

---

## 2. Buttons ✅/🔁

| Komponente | Klassen | Verwendung | Zustände |
|---|---|---|---|
| **Gold-CTA** | `.btn-cvs.btn-cvs--gold` | genau 1 Primär-CTA pro Sektion | hover: lift+glow · focus: Ring · disabled: 40 % |
| **Ghost** | `.btn-cvs.btn-cvs--ghost` | Sekundäraktionen | hover: Rand hell |
| Nav-Varianten | `.nav-btn-gold`, `.nav-btn-ghost` | nur in Nav | wie oben, kompakter |
| 🔁 Altlasten | `.plan-cta-free`, `.plan-cta-ghost` (crystals), `.pack-btn`, `.dp-btn-gold` (calendar), `.btn-gold/.btn-ghost` (ältere Seiten) | werden bei Migration durch `.btn-cvs`-Varianten ersetzt | — |

Regel: Buttons sind `<a>` bei Navigation, `<button>` bei Aktionen. Icon+Text erlaubt,
nie Icon-only ohne `aria-label`.

---

## 3. Struktur-Komponenten ✅

| Komponente | Klasse | Spezifikation |
|---|---|---|
| **Footer** | `.cvs-footer-master` | 3 Spalten (Produkt/Ressourcen/Rechtliches) + Legal-Bottom-Zeile. Wird Partial (D-005). |
| **Lightbar** | `.lightbar` | Gold-Separator zwischen Sektionen |
| **Aurora** | `.cvs-aurora` | Hintergrund-Orbs, position:fixed, hinter Content |
| **Scroll-Progress** | `#cvs-progress` | Goldleiste, width via JS |
| **Section-Header** | Eyebrow (gold, klein) + H2 + Subline | vor jeder Content-Sektion |
| **Reveal** | `.reveal` (+ Stagger-Index) | IntersectionObserver, einmalig |

---

## 4. Karten & Inhalte ✅/🔁

| Komponente | Heute | Ziel |
|---|---|---|
| Guide-Karte (Academy) | `.ac-card` + Kategorie-Filter + Modal | bleibt; Inhalte werden neu (Phase 3) |
| Plan-Karte (Kristalle) | eigene Klassen | 🔁 vereinheitlicht als `.cvs-card.cvs-card--plan` mit Highlight-Variante |
| Pack-Karte | `.pack-*` | 🔁 `.cvs-card--pack` inkl. Kristall-Icon (WebP) |
| Portfolio-Karte | Karten + Filter + Stat-Counter | bleibt (Referenzqualität) |
| Use-Case-/FAQ-Blöcke | vorhanden (crystals/academy) | FAQ als Akkordeon mit ARIA |

🆕 **`.cvs-card`-Basiskomponente:** Glass-BG, `--radius-lg`, Border `--glass-border`,
Hover-Lift — alle Kartentypen erben davon (bei Migration eingeführt).

---

## 5. Formulare & Feedback ✅/🆕

| Komponente | Status | Spezifikation |
|---|---|---|
| Input/Textarea/Select | ✅ (kontakt, login) | dunkle Felder, Label Pflicht, Fokus blau, Fehlerzeile rot-Textform |
| Passwort-Stärke-Balken | ✅ (login.php) | bleibt, Farben auf Tokens migrieren |
| **Toast** | ✅ (Studio) | oben rechts, auto-dismiss 4s, Erfolg/Fehler-Variante — 🆕 wird globales JS-Modul `assets/js/ui.js` |
| **Error-Box** | ✅ | inline unter Aktion, `[hidden]`-gesteuert (CSS-Fix aus Session 8 beachten!) |
| **Modal** | ✅ (Academy, Calendar) | 🆕 vereinheitlicht: Fokus-Falle, ESC, `aria-modal` |
| Honeypot-Feld | 🆕 | unsichtbares Feld für contact-submit (SECURITY.md §6) |
| Kristall-Kosten-Hinweis | 🆕 | „Diese Aktion kostet X 💎" + Balance danach — vor jeder kostenpflichtigen Aktion (CRYSTAL_SYSTEM.md §4) |

---

## 6. Studio-spezifische Komponenten (heute in studio-demo.php) ✅

Slot-Grid (Thumbnails + Status), Upload-Dropzone (Fortschritt), KI-Prompt-Panel
(Prompt + Stil + Generieren-Button + Task-Status-Poll), Render-Panel (Qualitäts-Toggle
720/1080 mit Plan-Gate, Fortschritt, Ergebnis-Player + Download), Job-Badge.
**Regel:** Diese Komponenten werden bei der Studio-Migration (Phase 2/3) in den Katalog
zurückdokumentiert (Klassen heute teils inline — Konsolidierung in `cvs-core.css`).

---

## 7. Verbindlichkeit

1. Erst Katalog, dann Code — neue Komponenten werden hier mit Name/Klassen/Zuständen eingetragen.
2. Eine Komponente = eine Implementierung. Duplikate (Abschnitt 2/4, 🔁) werden bei Seitenmigration getilgt.
3. `innerHTML`-Verbot gilt für alle Komponenten-JS (D-015): DOM-API/`<template>`.
4. Abnahme jeder Migration gegen die Referenzseite (QA_MASTERPLAN.md §5).
