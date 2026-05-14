# memory/business.md — Geschäftsmodell & Strategie

> Letzte Aktualisierung: 2026-05-14

---

## Produkt-Positionierung

**Cinematic Vision Studio** ist ein Browser-basiertes Video-Editing-Tool für Familien,
die professionell wirkende Erinnerungsvideos produzieren wollen — ohne Vorkenntnisse.

**Kern-Differenzierung:**
- KI-gestützte Bild/Szenen-Generierung direkt in die Video-Timeline integriert
- Serverseitiger Render (kein lokaler FFmpeg nötig)
- "Under 10 minutes" UX-Versprechen

---

## Zielgruppen

### Primär: Familiengedächtnis-Markt
- Eltern, die Urlaubsvideos für die Familie aufbereiten wollen
- Menschen, die Jahresrückblicke / Geburtstagsfilme erstellen
- Skill-Level: Digital-affin, aber keine Video-Profis
- Pain: iMovie/Premiere zu komplex, TikTok-Apps zu simpel/teen-oriented

### Sekundär: Content Creator (Hobbyisten)
- Ersteller cinematic Short-Videos (Instagram, YouTube Shorts)
- Lokale Unternehmen (Immobilien, Gastronomie) — einfache Produkt-Videos
- Digitale Nomaden, Travel-Blogger

---

## Erlösmodell (geplant)

### Freemium-Stufen

| Plan | Preis | Features | Limits |
|---|---|---|---|
| **Free** | $0 | Basis-Upload, 3 Szenen, 720p, kein Ton | Max 15s, ephemeral |
| **Starter** | ~$7–9/mo | 1080p, Audio, Persistent Storage, 10 Slots | Max 60s |
| **Pro** | ~$29/mo | KI-Generierungen (Kristalle inklusive), Priority Render | Max 5 min |
| **Studio** | ~$79/mo | API-Zugang, White-Label, Team-Features | Unbegrenzt |

### Kristalle (interne Währung)
- Einheit für KI-Generierungen (Kie.ai Credits weitergereicht + Marge)
- ~10–50 Credits pro Bild-Generierung (Kie.ai Backend-Kosten)
- Top-Up-Packs: 100 Kristalle = ~$4.99, 500 Kristalle = $17.99
- Aktuell: **Demo-Dummy** (keine echte Transaktion implementiert)

### Referral-Strategie
- `API_PROVIDER_LINK` in `includes/config.php` — Platzhalter für Kie.ai Referral
- Ziel: Provision auf KI-API-Nutzung über Affiliate

---

## Wettbewerbskontext

| Konkurrent | Stärke | Schwäche | Unser Vorteil |
|---|---|---|---|
| Canva Video | Große Brand | Kein echter FFmpeg-Render | Echter cinematic Output |
| CapCut | Mobile-native | Keine Server-Render | Desktop-Power |
| Adobe Express | Professional | Teuer, komplex | Simple UX |
| Runway | KI-first | Sehr teuer, Lernkurve | Familien-Fokus, Preis |

---

## Aktuelle Business-Metriken (Stand MVP)

| Metrik | Wert |
|---|---|
| **Version** | 0.1.0 |
| **Status** | Live (Free MVP) |
| **Zahlende Nutzer** | 0 (Pre-Revenue) |
| **Features live** | Upload → Render → Download (komplett) |
| **KI-Feature** | In Progress (Kie.ai Integration 80%) |

---

## Nächste Business-Entscheidungen

1. **Pricing validieren:** Workshop/Beta testen bevor Stripe-Integration
2. **Kristalle-System designen:** Wie viele Kristalle pro Funktion?
3. **Referral-Link aktivieren:** Kie.ai Affiliate-Programm prüfen
4. **Domain/Branding:** cinematic-studio-family → cinematic-vision-studio Domain
