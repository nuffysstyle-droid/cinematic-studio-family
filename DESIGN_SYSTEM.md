# DESIGN_SYSTEM.md — Cinematic Vision Studio
# Verbindliches Design-System v1.0 „Black · Electric Blue · Cinematic Gold"

| Feld | Wert |
|---|---|
| Version | 1.0 (Architecture Lock, 2026-07-05) — D-011 |
| Quelle der Wahrheit | `assets/css/cvs-core.css` (Tokens) + `immobilienvideos.html` (Referenz-Implementierung) |
| Geltung | JEDE Seite und JEDES Modul der Plattform — auch Login, Dashboard, Studio, Admin |
| Verboten | Neue Hex-Werte, Google-Fonts-CDN, Insel-Stylesheets, Inline-Farbwerte |

---

## 1. Markenkern

**Gefühl:** Kino-Premiere, nicht Web-Tool. Dunkler Saal (Black), elektrisches Licht (Blue),
goldener Moment (Gold). Jede Seite inszeniert Inhalte wie einen Film: Aurora-Licht im
Hintergrund, Film-Grain-Textur, goldene Akzente ausschließlich für Handlung/Wert.

**Tonalität (Text):** Deutsch, direkt, souverän, „du"-Ansprache. Keine Ausrufezeichen-Ketten,
keine Marketing-Floskeln. Fachbegriffe erklärt (Zielgruppe: keine Video-Profis).

---

## 2. Farb-Tokens (verbindlich, aus cvs-core.css)

| Token | Wert | Verwendung |
|---|---|---|
| `--black` | `#020205` | Seitenhintergrund (Body) |
| `--black-2` | `#06060f` | Sektionen-Wechsel, Cards-Untergrund |
| `--black-3` | `#09091a` | Erhöhte Flächen |
| `--glass-bg` | `rgba(5,8,24,.78)` | Glas-Panels, Nav-Hintergrund |
| `--glass-border` | `rgba(24,114,255,.18)` | Panel-Ränder |
| `--blue-core` | `#003ee8` | Tiefe Blau-Flächen, Aurora-Basis |
| `--blue-bright` | `#1872ff` | Interaktions-Blau, Links, Fokus |
| `--blue-glow` | `#4da0ff` | Glows, Hover-Licht |
| `--blue-pale` | `rgba(77,160,255,.10)` | Flächen-Tints |
| `--gold` | `#b8942e` | Gold-Basis (Text auf dunkel) |
| `--gold-warm` | `#d4a93c` | Gold-Verlauf Mitte |
| `--gold-bright` | `#e8c355` | CTA-Highlights, Eyebrows |
| `--gold-light` | `#f2d878` | Verlaufs-Spitzlicht |
| `--gold-glow` | `rgba(200,160,60,.4)` | CTA-Schatten/Glow |
| `--white` | `#edf2ff` | Primärtext |
| `--white-dim` | `rgba(237,242,255,.52)` | Sekundärtext |
| `--white-faint` | `rgba(237,242,255,.10)` | Trennlinien, Ghost-Ränder |

**Regeln:**
1. **Gold = Wert & Handlung.** Nur für: primäre CTAs, Preise/Kristalle, Eyebrows, aktive Akzente. Nie für Fließtext-Flächen.
2. **Blau = System & Interaktion.** Links, Fokus-Ringe, Info-Zustände, Aurora.
3. **Semantik-Zusätze** (einzige erlaubte Erweiterung, bei Bedarf als Token nachziehen): Erfolg = grünstichiges Blau vermeiden → definierte Zustandsfarben werden bei Erstbedarf als `--ok/--warn/--err`-Tokens in cvs-core.css ergänzt (ein Commit, dokumentiert hier).
4. **Abweichende Bestandswerte** (z. B. `#f59e0b` in login.php, `#f5c518` in index.html) sind Altlasten → werden bei Migration durch Tokens ersetzt, niemals kopiert.

---

## 3. Typografie

| Rolle | Font | Gewicht | Verwendung |
|---|---|---|---|
| Display/Headlines | **Syne** | 500/600 (lokal: Syne-500/600.ttf) | H1–H3, Nav-Wortmarke, CTAs (900 via synthetischem Bold vermeiden — nur echte Schnitte) |
| Text/UI | **DM Sans** | 300/400/500/600 (lokal) | Body, Formulare, Tabellen, Buttons sekundär |

**Regeln:** Nur lokale Fonts aus `assets/fonts/fonts.css` (DSGVO + Performance).
Hierarchie: H1 einmal pro Seite; Eyebrow (klein, gold, tracking-weit) über H1/H2 als
Sektions-Anker. Zeilenlänge Fließtext ≤ ~70 Zeichen. Mindestgröße UI-Text 14px, Body 16px.

---

## 4. Atmosphäre-Schichten (die CVS-Signatur)

| Schicht | Technik | Regeln |
|---|---|---|
| **Film-Grain** | `body::after`, SVG-Fractal-Noise | Referenzwerte: `opacity:.28`, `z-index:0`, `pointer-events:none`. Inhalt liegt auf `z-index:1+`. (Achtung: cvs-core.css enthält noch eine ältere `body::before`-Variante mit `.5/z-2` — Referenzseiten überschreiben korrekt; bei Konsolidierung wird cvs-core auf Referenzwerte angepasst.) |
| **Aurora** | `.cvs-aurora` mit CSS-Orbs (blur, animiert) | CSS statt Canvas (Batterie/CPU). Max. 3 Orbs, Bewegung langsam (≥ 20s Loops). |
| **Lightbar** | `.lightbar` | Goldener Licht-Separator zwischen Sektionen; 1× zwischen thematischen Blöcken, nicht nach jedem Absatz. |
| **Scroll-Progress** | `#cvs-progress` | 2–3px Goldleiste oben, JS-gebunden an Scrolltiefe. Auf jeder Content-Seite. |
| **Custom Cursor** | in cvs-core vorhanden | **DEAKTIVIERT** (Session-11-Entscheidung, `cursor:auto!important`). Bleibt deaktiviert — Usability > Gimmick. |

---

## 5. Geometrie & Abstände

| Token | Wert | Verwendung |
|---|---|---|
| `--radius-sm` | 6px | Inputs, Chips |
| `--radius-md` | 14px | Buttons, Cards klein |
| `--radius-lg` | 26px | Panels, Hero-Karten |
| Spacing-Raster | 4er-Basis (4/8/12/16/24/32/48/64/96) | Kein Pixel-Freestyle |
| Content-Breite | max ~1200px zentriert | Sektionen mit Innenabstand ≥ 24px mobil / 48px Desktop |

---

## 6. Motion

| Prinzip | Wert |
|---|---|
| Standard-Easing | `--ease-smooth: cubic-bezier(.23,1,.32,1)` |
| Reveal-Animationen | `.reveal` + Staggering (Karten nacheinander, 60–100ms Versatz), einmalig beim ersten Sichtbarwerden (IntersectionObserver) |
| Hover | Translate ≤ 2px + Glow; nie Layout-Shift |
| Dauer | Micro 150–250ms · Sektions-Reveal 400–700ms · Ambient (Aurora) ≥ 20s |
| Reduced Motion | `prefers-reduced-motion: reduce` → Reveals sofort sichtbar, Aurora statisch (bei Migration nachrüsten — heute nicht implementiert) |

---

## 7. Responsive Regeln

| Breakpoint | Verhalten |
|---|---|
| < 600px | Single-Column-Stack, Touch-Targets ≥ 44px, CTAs volle Breite |
| < 900px (Nav) | Burger → Vollbild-Overlay (`.mob-nav`), CTA zuoberst |
| ≥ 1200px | Volle Layouts, max-width greift |

Bilder: WebP bevorzugt, `loading="lazy"` unterhalb des Folds, explizite width/height gegen CLS.
Hero-Video: `preload="metadata"` + `poster` (Phase-1-Fix), nie Autoplay mit Ton.

---

## 8. Barrierefreiheit (Minimum)

1. Kontrast: Primärtext auf Black ≥ 7:1 (erfüllt), Sekundärtext ≥ 4,5:1 prüfen (white-dim grenzwertig auf hellen Panels).
2. Fokus sichtbar: Blue-Fokusring, niemals `outline:none` ohne Ersatz.
3. Alt-Texte für alle inhaltstragenden Bilder; dekorative Bilder `alt=""`.
4. Formulare: Label immer, Fehler in Textform (nicht nur Farbe).
5. Tastatur: Nav, Modals, Akkordeons vollständig bedienbar; Modals mit Fokus-Falle + ESC.

---

## 9. Anwendung & Abnahme

- **Jede neue/migrierte Seite** wird gegen `immobilienvideos.html` visuell reviewt
  (Nav, Grain, Aurora, Buttons, Footer, Reveals, Mobile) — Checkliste in QA_MASTERPLAN.md §5.
- **Design-Änderungswünsche** laufen über dieses Dokument (Token/Regel zuerst ändern,
  dann implementieren) — nie ad hoc im Code.
- Komponenten-Details: [COMPONENT_LIBRARY.md](COMPONENT_LIBRARY.md).
