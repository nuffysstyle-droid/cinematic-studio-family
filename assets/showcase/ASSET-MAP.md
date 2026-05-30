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
| `assets/showcase/hero/studio-header.jpg` | Bild | ~1.5 MB | Hero-Hintergrund |
| `assets/showcase/hero/studio-header-sm.jpg` | Bild | — | Portfolio Karte #1 (Fallback) |
| `assets/showcase/hero/showreel.mp4` | Video | ~7.6 MB | Hero-Video / spätere Demo |
| `assets/showcase/portfolio/scifi-music-v1.jpg` | Bild | ~34 KB | Portfolio Karte #2 (Orbit Protocol) |
| `assets/showcase/portfolio/golden-social-v1.jpg` | Bild | ~34 KB | Portfolio Karte #3 (Tokyo Dusk) |
| `assets/showcase/portfolio/tiktok-viral-v1.jpg` | Bild | ~42 KB | Portfolio Karte #4 (Midnight Fizz) |
| `assets/showcase/portfolio/product-ad-v1.jpg` | Bild | ~23 KB | Portfolio Karte #5 (Luminex Pro) |
| `assets/showcase/portfolio/anime-transform-v1.jpg` | Bild | ~35 KB | Portfolio Karte #6 (Sakura Shift) |
| `assets/showcase/portfolio/car-luxury-v1.jpg` | Bild | ~26 KB | Portfolio Karte #7 (Apex GT Reveal) |
| `assets/showcase/portfolio/noir-trailer-v1.jpg` | Bild | ~19 KB | Portfolio Karte #1 (Nacht & Neon) |
| `assets/showcase/portfolio/restoration-v1.jpg` | Bild | ~58 KB | Portfolio Karte #8 (Summer Memories 4K) |
| `assets/showcase/portfolio/fx-transform-v1.jpg` | Bild | ~49 KB | Portfolio Karte #9 (Phoenix Rise) |
| `assets/showcase/portfolio/hyperreal-ad-v1.jpg` | Bild | ~25 KB | Portfolio Karte #10 (Pure Essence) |
| `assets/showcase/shop/starter-crystal-cover.jpg` | Bild | ~22 KB | Shop Paket #1 (Starter Crystal) |
| `assets/showcase/shop/tiktok-viral-cover.jpg` | Bild | ~15 KB | Shop Paket #2 (TikTok Viral Pack) |
| `assets/showcase/shop/product-ad-cover.jpg` | Bild | ~16 KB | Shop Paket #3 (Product Ad Pro) |
| `assets/showcase/shop/anime-shift-cover.jpg` | Bild | ~24 KB | Shop Paket #4 (Anime Shift) |
| `assets/showcase/shop/pro-bundle-cover.jpg` | Bild | ~25 KB | Shop Paket #5 (Pro Studio Bundle) |
| `assets/showcase/thumbnails/viral-hook-v1.jpg` | Bild | ~24 KB | KI-Video #1 (Viral Hook Clip) |
| `assets/showcase/thumbnails/product-spotlight-v1.jpg` | Bild | ~6 KB | KI-Video #2 (Product Spotlight) |
| `assets/showcase/thumbnails/cinematic-track-v1.jpg` | Bild | ~28 KB | KI-Video #3 (Cinematic Track) |
| `assets/showcase/branding/studio-logo-wide.png` | Bild | ~339 KB | Branding / Footer |
| `assets/cvs-logo.png` | Bild | — | Global: Favicon + Nav |
| `assets/cvs-hero-studio.jpg` | Bild | — | Homepage Hero-Hintergrund |
| `assets/cvs-hero-loop.mp4` | Video | — | Homepage Hero-Video |

---

## Portfolio-Karten — Asset-Zuordnung

| # | Karte | Klasse | Ziel-Asset | Status |
|---|---|---|---|---|
| 1 | **Nacht & Neon** | `noir` | `portfolio/noir-trailer-v1.jpg` | ✅ Generiert |
| 2 | **Orbit Protocol** | `scifi` | `portfolio/scifi-music-v1.jpg` | ✅ Vorhanden |
| 3 | **Tokyo Dusk** | `golden` | `portfolio/golden-social-v1.jpg` | ✅ Generiert |
| 4 | **Midnight Fizz** | `tiktok` | `portfolio/tiktok-viral-v1.jpg` | ✅ Generiert |
| 5 | **Luminex Pro** | `product` | `portfolio/product-ad-v1.jpg` | ✅ Generiert |
| 6 | **Sakura Shift** | `anime` | `portfolio/anime-transform-v1.jpg` | ✅ Generiert |
| 7 | **Apex GT Reveal** | `car` | `portfolio/car-luxury-v1.jpg` | ✅ Generiert |
| 8 | **Summer Memories 4K** | `beforeafter` | `portfolio/restoration-v1.jpg` | ✅ Generiert |
| 9 | **Phoenix Rise** | `char` | `portfolio/fx-transform-v1.jpg` | ✅ Generiert |
| 10 | **Pure Essence** | `hyperreal` | `portfolio/hyperreal-ad-v1.jpg` | ✅ Generiert |

**CSS-Fallback:** Alle Karten haben einen CSS-Gradient-Hintergrund. Sobald ein `img.pf-frame-img` eingefügt und `.has-image` gesetzt wird, ersetzt das Bild den Gradienten.

---

## Shop-Pakete — Asset-Zuordnung

| Paket | Ziel-Asset | Status |
|---|---|---|
| Starter Crystal | `shop/starter-crystal-cover.jpg` | ✅ Generiert |
| TikTok Viral Pack | `shop/tiktok-viral-cover.jpg` | ✅ Generiert |
| Product Ad Pro | `shop/product-ad-cover.jpg` | ✅ Generiert |
| Anime Shift | `shop/anime-shift-cover.jpg` | ✅ Generiert |
| Pro Studio Bundle | `shop/pro-bundle-cover.jpg` | ✅ Generiert |

---

## KI-Videos — Asset-Zuordnung

| # | Format | Ziel-Asset | Status |
|---|---|---|---|
| 1 | Viral Hook Clip | `thumbnails/viral-hook-v1.jpg` | ✅ Generiert |
| 2 | Product Spotlight | `thumbnails/product-spotlight-v1.jpg` | ✅ Generiert |
| 3 | Cinematic Track | `thumbnails/cinematic-track-v1.jpg` | ✅ Generiert |

**Hinweis:** KI-Videos nutzen aktuell CSS-Thumbnail-Fallbacks (`thumb-viral`, `thumb-commercial`, `thumb-music`). Echte Vorschaubilder oder kurze MP4-Loops würden die Seite deutlich aufwerten.

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
| 1 | Nacht & Neon | `noir-trailer-v1.jpg` | *Rain-soaked detective, cinematic drone shot, neon reflections, golden hour grade, film noir atmosphere, wet cobblestone streets, dramatic shadows, 16:9 composition* | ✅ Generiert |
| 2 | Orbit Protocol | `scifi-music-v1.jpg` | *Epic spacecraft drifting through a vibrant purple nebula, cinematic wide shot, volumetric lighting, lens flare, deep space atmosphere, ultra detailed, dark background with cyan and purple accents* | ✅ Generiert |
| 3 | Tokyo Dusk | `golden-social-v1.jpg` | *Tokyo cityscape rooftop view at golden hour, warm amber sunlight reflecting on glass buildings, cinematic color grading, subtle lens flare, urban photography style, shallow depth of field* | ✅ Generiert |
| 4 | Midnight Fizz | `tiktok-viral-v1.jpg` | *Neon pink and blue studio lighting, close-up of a soda can opening with dramatic fizz splash, slow motion feel, high contrast, dark background with colorful backlighting* | ✅ Generiert |
| 5 | Luminex Pro | `product-ad-v1.jpg` | *Sleek wireless headphones on a white marble surface, soft professional studio lighting, premium product photography, subtle shadow, minimalist composition, high-end commercial look* | ✅ Generiert |
| 6 | Sakura Shift | `anime-transform-v1.jpg` | *Portrait morphing into anime style, sakura petals falling, dreamy pastel sky, soft bokeh background, cinematic lighting, pastel color grading* | ✅ Generiert |
| 7 | Apex GT Reveal | `car-luxury-v1.jpg` | *Matte black sports car in underground garage, dramatic rim light, slow push-in feel, metallic reflections, cinematic color grading, dark moody atmosphere* | ✅ Generiert |
| 8 | Summer Memories 4K | `restoration-v1.jpg` | *Family picnic footage upgraded to cinematic 4K, color restoration, film grain added, warm sunlight, nostalgic to modern transformation, vivid greens and warm skin tones, 16:9 composition* | ✅ Generiert |
| 9 | Phoenix Rise | `fx-transform-v1.jpg` | *Ordinary person transforms into fire-wielding hero, particle effects, cinematic showdown, dramatic backlight, golden fire particles, dark background with warm orange glow, 16:9 composition* | ✅ Generiert |
| 10 | Pure Essence | `hyperreal-ad-v1.jpg` | *Luxury perfume bottle catching morning light, water droplets, ultra-sharp macro, premium feel, soft studio lighting, high-end commercial photography, minimalist composition, 16:9 composition* | ✅ Generiert |

**Hinweis:** Prompts sind für Kie.ai Flux Kontext Pro/Max optimiert. Generierung erfordert ~10–20 Kristalle pro Bild. Assets sollten nach Generierung durch ImageMagick oder Squoosh komprimiert werden (`mogrify -quality 85 -resize 640x360`).

---

## Shop Asset Generation Prompts

Spezifikation für alle generierten Shop-Assets:
- **Format:** JPG (Qualität 85)
- **Auflösung:** 480×270 px (16:9)
- **Max-Größe:** 200 KB
- **Zielordner:** `assets/showcase/shop/`

| Paket | Dateiname | Kie.ai Prompt | Status |
|---|---|---|---|
| Starter Crystal | `starter-crystal-cover.jpg` | *Glowing crystal formation on dark cinematic background, volumetric light rays, deep blue and gold accents, premium digital art style, mysterious atmosphere, 16:9 composition* | 🟡 Prompt bereit |
| TikTok Viral Pack | `tiktok-viral-cover.jpg` | *Neon-lit smartphone screen showing viral video metrics, pink and cyan studio lighting, energetic composition, social media aesthetic, dark background, 16:9 composition* | 🟡 Prompt bereit |
| Product Ad Pro | `product-ad-cover.jpg` | *Professional product photography setup with ring light, camera on tripod, sleek minimal desk, soft shadows, premium studio environment, 16:9 composition* | 🟡 Prompt bereit |
| Anime Shift | `anime-shift-cover.jpg` | *Split portrait half realistic half anime style, pastel color grading, sakura petals, dreamy bokeh, artistic transformation visual, 16:9 composition* | 🟡 Prompt bereit |
| Pro Studio Bundle | `pro-bundle-cover.jpg` | *Professional film studio control room with multiple monitors, cinematic color grading interface, dark moody lighting, premium tech aesthetic, 16:9 composition* | 🟡 Prompt bereit |

---

## KI-Video Asset Generation Prompts

Spezifikation für alle generierten KI-Video-Thumbnail-Assets:
- **Format:** JPG (Qualität 85)
- **Auflösung:** 480×270 px (16:9) — gleiche Specs wie Shop
- **Max-Größe:** 200 KB
- **Zielordner:** `assets/showcase/thumbnails/`

| # | Format | Dateiname | Kie.ai Prompt | Status |
|---|---|---|---|---|
| 1 | Viral Hook Clip | `viral-hook-v1.jpg` | *Young dancer in neon-lit urban alley, TikTok style vertical video frame, energetic pose, pink and blue backlight, viral energy, cinematic motion blur, 16:9 composition* | ✅ Generiert |
| 2 | Product Spotlight | `product-spotlight-v1.jpg` | *Sleek product on rotating pedestal with dramatic spotlight, dark studio background, commercial photography look, premium feel, 16:9 composition* | ✅ Generiert |
| 3 | Cinematic Track | `cinematic-track-v1.jpg` | *Musician silhouette against massive LED wall with abstract visuals, concert atmosphere, dramatic fog and stage lights, music video aesthetic, 16:9 composition* | ✅ Generiert |

---

## Generierungs-Prioritäten & Batch-Plan

> Empfohlene Reihenfolge für zukünftige Batch-Generierungen. Pro Batch ~5 Bilder, um Kontext-Limits zu vermeiden.

| Batch | Assets | Anzahl | Geschätzte Kristalle | Impact | Blocker |
|---|---|---|---|---|---|
| **Batch A** | Portfolio #3–#7 (Tokyo Dusk … Apex GT) | 5 Bilder | ~50–100 | 🔥 Sehr hoch | ✅ Abgeschlossen |
| **Batch B** | Portfolio #1, #8–#10 (Nacht&Neon … Pure Essence) | 4 Bilder | ~40–80 | 🔥 Sehr hoch | ✅ Abgeschlossen |
| **Batch C** | Shop #1–#5 (alle Pakete) | 5 Bilder | ~50–100 | 🔥 Hoch | ✅ Abgeschlossen |
| **Batch D** | KI-Videos #1–#3 (alle Thumbnails) | 3 Bilder | ~30–60 | Mittel | ✅ Abgeschlossen |

**Gesamtkosten:** ~170–340 Kristalle für alle fehlenden Assets (17 Bilder).

---

## Integration in HTML — Referenz

### Portfolio-Karte mit Bild

```html
<div class="pf-frame noir has-image">
  <img class="pf-frame-img" src="assets/showcase/portfolio/noir-trailer-v1.jpg" alt="Nacht & Neon" loading="lazy" decoding="async" onerror="this.style.display='none';this.parentElement.classList.remove('has-image');">
  <div class="pf-frame-meta"> ... </div>
</div>
```

### Shop-Paket mit Cover-Bild

```html
<div class="cvs-thumb thumb-starter has-image">
  <img class="cvs-thumb-img" src="assets/showcase/shop/starter-crystal-cover.jpg" alt="Starter Crystal" loading="lazy" decoding="async" onerror="this.style.display='none';this.parentElement.classList.remove('has-image');">
  <span class="cvs-thumb-icon">💎</span>
  <span class="cvs-badge recommended">Empfohlen</span>
</div>
```

### KI-Video mit Thumbnail

```html
<div class="cvs-thumb thumb-viral has-image">
  <img class="cvs-thumb-img" src="assets/showcase/thumbnails/viral-hook-v1.jpg" alt="Viral Hook Clip" loading="lazy" decoding="async" onerror="this.style.display='none';this.parentElement.classList.remove('has-image');">
  <span class="cvs-thumb-icon">🎵</span>
  <div class="play-overlay"><span class="play-btn">▶</span></div>
</div>
```

---

*Letzte Aktualisierung: 2026-05-30 | Session 12 — Alle Batches abgeschlossen (A–D)*
