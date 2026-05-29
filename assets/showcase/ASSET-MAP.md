# CVS Showcase Asset Map

> Master-Referenz für alle Showcase-Assets.  
> Neue Assets **immer** in `assets/showcase/` ablegen — keine Leerzeichen, keine Umlaute in Dateinamen.

---

## Ordnerstruktur

```
assets/showcase/
├── hero/              → Hero-Bilder & Showreel-Videos
├── portfolio/         → Portfolio-Karten (16:9 Thumbnails)
├── shop/              → Shop-Produktbilder
├── thumbnails/        → Kleine Preview-Thumbnails
└── branding/          → Logos, Watermarks, Brand-Assets
```

## Namenskonvention

| Element | Format | Beispiel |
|---|---|---|
| Portfolio-Karten | `portfolio/{slug}-{variant}.jpg` | `portfolio/noir-trailer-v1.jpg` |
| Shop-Pakete | `shop/{pack-slug}-cover.jpg` | `shop/starter-crystal-cover.jpg` |
| Thumbnails | `thumbnails/{slug}-tn.jpg` | `thumbnails/noir-trailer-tn.jpg` |
| Hero-Assets | `hero/{name}.{ext}` | `hero/showreel.mp4` |

---

## Vorhandene Assets ✅

| Pfad | Typ | Größe | Verwendung |
|---|---|---|---|
| `assets/showcase/hero/studio-header.jpg` | Bild | ~1.5 MB | Hero / 1. Portfolio-Karte |
| `assets/showcase/hero/showreel.mp4` | Video | ~7.6 MB | Hero-Video / spätere Demo |
| `assets/showcase/branding/studio-logo-wide.png` | Bild | ~339 KB | Branding / Footer |
| `assets/cvs-logo.png` | Bild | — | Global: Favicon + Nav |
| `assets/cvs-hero-studio.jpg` | Bild | — | Homepage Hero-Hintergrund |
| `assets/cvs-hero-loop.mp4` | Video | — | Homepage Hero-Video |

---

## Portfolio-Karten — Asset-Zuordnung

| # | Karte | Klasse | Ziel-Asset | Status |
|---|---|---|---|---|
| 1 | **Nacht & Neon** | `noir` | `portfolio/noir-trailer-v1.jpg` | 🟡 Platzhalter: `studio-header.jpg` als Fallback |
| 2 | **Orbit Protocol** | `scifi` | `portfolio/scifi-music-v1.jpg` | ⬜ Offen |
| 3 | **Tokyo Dusk** | `golden` | `portfolio/golden-social-v1.jpg` | ⬜ Offen |
| 4 | **Midnight Fizz** | `tiktok` | `portfolio/tiktok-viral-v1.jpg` | ⬜ Offen |
| 5 | **Luminex Pro** | `product` | `portfolio/product-ad-v1.jpg` | ⬜ Offen |
| 6 | **Sakura Shift** | `anime` | `portfolio/anime-transform-v1.jpg` | ⬜ Offen |
| 7 | **Apex GT Reveal** | `car` | `portfolio/car-luxury-v1.jpg` | ⬜ Offen |
| 8 | **Summer Memories 4K** | `beforeafter` | `portfolio/restoration-v1.jpg` | ⬜ Offen |
| 9 | **Phoenix Rise** | `char` | `portfolio/fx-transform-v1.jpg` | ⬜ Offen |
| 10 | **Pure Essence** | `hyperreal` | `portfolio/hyperreal-ad-v1.jpg` | ⬜ Offen |

**CSS-Fallback:** Alle Karten haben einen CSS-Gradient-Hintergrund. Sobald ein `img.pf-frame-img` eingefügt und `.has-image` gesetzt wird, ersetzt das Bild den Gradienten.

---

## Shop-Pakete — Asset-Zuordnung

| Paket | Ziel-Asset | Status |
|---|---|---|
| Starter Crystal | `shop/starter-crystal-cover.jpg` | ⬜ Offen |
| TikTok Viral Pack | `shop/tiktok-viral-cover.jpg` | ⬜ Offen |
| Product Ad Pro | `shop/product-ad-cover.jpg` | ⬜ Offen |
| Anime Shift | `shop/anime-shift-cover.jpg` | ⬜ Offen |
| Pro Studio Bundle | `shop/pro-bundle-cover.jpg` | ⬜ Offen |

---

## Empfohlene Specs für spätere Assets

| Typ | Format | Auflösung | Max-Größe | Notizen |
|---|---|---|---|---|
| Portfolio-Karten | JPG/WEBP | 640×360 (16:9) | 300 KB | `object-fit: cover` |
| Shop-Covers | JPG/WEBP | 480×270 (16:9) | 200 KB | Karten-Preview |
| Thumbnails | JPG/WEBP | 320×180 | 80 KB | Lazy-Load fallback |
| Hero-Videos | MP4 (H.264) | 1920×1080 | 5 MB | Loop, muted, no audio |

---

## Integration in HTML

### Portfolio-Karte mit Bild

```html
<div class="pf-frame noir has-image">
  <img class="pf-frame-img" src="assets/showcase/portfolio/noir-trailer-v1.jpg" alt="Nacht & Neon" loading="lazy">
  <div class="pf-frame-meta"> ... </div>
</div>
```

### Ohne Bild (CSS-Fallback)

```html
<div class="pf-frame noir">
  <div class="pf-frame-meta"> ... </div>
</div>
```

---

## Portfolio Asset Generation Prompts (Session 10)

Spezifikation für alle generierten Assets:
- **Format:** JPG (Qualität 85)
- **Auflösung:** 640×360 px (16:9)
- **Max-Größe:** 300 KB
- **Zielordner:** `assets/showcase/portfolio/`

| # | Karte | Dateiname | Kie.ai Prompt | Status |
|---|---|---|---|---|
| 1 | Nacht & Neon | `noir-trailer-v1.jpg` | *(Fallback aktiv — siehe oben)* | 🟡 Fallback |
| 2 | Orbit Protocol | `scifi-music-v1.jpg` | *Epic spacecraft drifting through a vibrant purple nebula, cinematic wide shot, volumetric lighting, lens flare, deep space atmosphere, ultra detailed, dark background with cyan and purple accents* | 🟡 Prompt bereit |
| 3 | Tokyo Dusk | `golden-social-v1.jpg` | *Tokyo cityscape rooftop view at golden hour, warm amber sunlight reflecting on glass buildings, cinematic color grading, subtle lens flare, urban photography style, shallow depth of field* | 🟡 Prompt bereit |
| 4 | Midnight Fizz | `tiktok-viral-v1.jpg` | *Neon pink and blue studio lighting, close-up of a soda can opening with dramatic fizz splash, slow motion feel, high contrast, dark background with colorful backlighting* | 🟡 Prompt bereit |
| 5 | Luminex Pro | `product-ad-v1.jpg` | *Sleek wireless headphones on a white marble surface, soft professional studio lighting, premium product photography, subtle shadow, minimalist composition, high-end commercial look* | 🟡 Prompt bereit |

**Hinweis:** Prompts sind für Kie.ai Flux Kontext Pro/Max optimiert. Generierung erfordert ~10–20 Kristalle pro Bild. Assets sollten nach Generierung durch ImageMagick oder Squoosh komprimiert werden (`mogrify -quality 85 -resize 640x360`).

---

*Letzte Aktualisierung: 2026-05-29 | Session AD + AI-2*
