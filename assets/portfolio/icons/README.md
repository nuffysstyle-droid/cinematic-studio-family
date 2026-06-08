# 3D Cinematic Icons — Render-Assets & Arbeitsanweisung

> Diese Datei ist das Gedächtnis für die Icon-Integration auf `immobilienvideos.html`.
> Bei neuem Render: Datei nach unten stehendem Workflow verarbeiten, fertig.

---

## STATUS (Stand 2026-06-03)

Alle gelieferten Renders sind HUD-Ring-Style auf **purem Schwarz** → alle per `screen`-Blend (`ico-blend`).
Beide Sektionen (Leistungen + Vorteile) sind jetzt **frei schwebend** (kein Orbit-Ring mehr), Icons ~186px.

| # | Datei | Motiv | Status |
|---|---|---|---|
| 1 | `ico-video.webp`   | Cinema-Kamera (HUD-Ring) | ✅ FERTIG |
| 2 | `ico-drone.webp`   | Drohne im HUD-Ring, „CVS" | ✅ FERTIG |
| 3 | `ico-360.webp`     | 360° Gold-Text + Orbit-Pfeil, HUD-Ring | ✅ FERTIG |
| 4 | `ico-post.webp`    | Gold-Torus-Ring + Symbole (Post-Production) | ✅ FERTIG |
| 5 | `ico-reach.webp`   | Balkendiagramm + blauer Pfeil, HUD-Ring | ✅ FERTIG |
| 6 | `ico-story.webp`   | Monitor mit „CVS", HUD-Ring (Storytelling) | ✅ FERTIG |
| 7 | `ico-consult.webp` | Personen-Interaktion + Sprechblase (Beratung) | ✅ FERTIG |
| 8 | `ico-concept.webp` | Klemmbrett + Häkchen, HUD-Ring (Konzept) | ✅ FERTIG |
| 9 | `ico-speed.webp`   | Energie-Blitz, HUD-Ring (Tempo) | ✅ FERTIG |
| 10| `ico-quality.webp` | Shield + Häkchen, HUD-Ring (Qualität) | ✅ FERTIG |

**ALLE 10 ICONS FERTIG ✅** — alle Render-Icons eingebaut (768px WebP, `screen`-Blend, frei schwebend).

## Ablauf-Nummern (Timeline 01/02/03) — FERTIG ✅
`ico-step1.webp` (01, schwarz→`screen`), `ico-step2.webp` (02, schwarz→`screen`), `ico-step3.webp` (03, transparent→ohne Blend).
Klasse `.step-img`; ersetzen die alten Text-Nummern in der `.steps`-Timeline. Verbindungslinie (`.steps::before`) entfernt.

Bis ein Render existiert, zeigt das jeweilige Icon automatisch ein SVG-Fallback
(per `onload`-Swap in `immobilienvideos.html`).

---

## RENDER-SPEZIFIKATION (für den User beim Erstellen)
- **Auflösung:** so hoch wie möglich (4096² ideal) — wird sauber runterskaliert.
- **Hintergrund:** ENTWEDER **transparent** (Bevorzugt, wie Drohne) ODER **pures Schwarz #000** (wie Kamera).
  Grau/Studio-BG NICHT verwenden — lässt sich nicht sauber ausblenden.
- **Objekt:** zentriert, quadratisches Bild, kein Fremdtext (CVS-Branding ok).
- **Look:** Metallic Gold + Electric Blue, Glas/Chrom, HDR-Reflexe, Bloom. Hollywood/Stark-HUD, nicht Gaming.

---

## EINBAU-WORKFLOW (das mache ich pro Render)

Quelle liegt meist in `C:/Users/User/Downloads/` (neueste PNG) oder direkt im Icons-Ordner.

### A) Transparenter Hintergrund (Bevorzugt)
```bash
ffmpeg -y -i "QUELLE.png" -vf "scale=768:768:flags=lanczos" \
  -c:v libwebp -lossless 0 -q:v 92 "assets/portfolio/icons/ico-XXX.webp" -loglevel error
```
- `<img class="ico-asset" ...>` (KEINE `ico-blend`-Klasse).
- Falls Objekt sehr breit (Rotoren etc.): vorher quadratisch transparent padden:
  `-vf "format=rgba,pad=1900:1900:(ow-iw)/2:(oh-ih)/2:color=#00000000,scale=768:768"`

### B) Reiner schwarzer Hintergrund (#000)
```bash
ffmpeg -y -i "QUELLE.png" -vf "scale=768:768:flags=lanczos" \
  -c:v libwebp -lossless 0 -q:v 92 "assets/portfolio/icons/ico-XXX.webp" -loglevel error
```
- `<img class="ico-asset ico-blend" ...>`  ← `ico-blend` = `mix-blend-mode:screen`, blendet Schwarz weg.

### NICHT machen
- ❌ `colorkey` zum BG-Entfernen → ausgefranste/pixelige Kanten.
- ❌ grauen/Studio-BG einbauen → sichtbares Rechteck.

---

## CSS-FRAMING (bereits in immobilienvideos.html aktiv)
- Frei schwebend, KEIN Kreis/Rahmen. `.feat-ico` 188px, `.ico-asset` 186px, `object-fit:contain`.
- Glow-Disc dahinter (`.feat-ico::after`), Hover: leichtes Anheben + blau-goldener Glow.
- `.ico-blend{mix-blend-mode:screen}` nur für Schwarz-BG-Renders.
- Vorteile-Icons (7–10) sitzen aktuell noch in Orbit-Discs (`.adv-disc`, cover) — bei Render ggf. wie Leistungen frei stellen (mit User klären).

## Auto-Swap-Mechanik
`<img ... onload="this.style.display='block';this.nextElementSibling.style.display='none'">`
→ Render lädt = Bild sichtbar, SVG-Fallback aus. Datei fehlt = SVG bleibt.
WICHTIG: KEIN `loading="lazy"` (verhindert Laden bei `display:none`).

## Parkplatz
- `ico-video_BG_alt.webp.bak` = erste Kamera mit Studio-BG (verworfen, nicht nutzen).
- `3d kamaer.png`, `DROHNE GPT ....png` = Original-Quellen (unreferenziert).
