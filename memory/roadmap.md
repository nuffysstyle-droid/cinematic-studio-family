# memory/roadmap.md — Produkt-Roadmap

> Letzte Aktualisierung: 2026-05-14

---

## Status-Legende
✅ Abgeschlossen · 🟡 In Progress · ⬜ Geplant · 🧊 Frozen (bewusst pausiert) · ❌ Abgebrochen

---

## V0.1.0 — Free MVP ✅ LIVE (2026-05-13)

**Ziel:** Vollständiger Upload→Edit→Render→Download-Flow ohne KI, kostenlos nutzbar.

| Feature | Status |
|---|---|
| Video-Upload + MIME-Validierung | ✅ |
| Slot-Analyse via FFmpeg | ✅ |
| Slot-Replacement: Text-Titelkarte (drawtext) | ✅ |
| Slot-Replacement: Bild | ✅ |
| Slot-Replacement: Video | ✅ |
| Finaler Render → MP4 (720p, ultrafast, kein Ton) | ✅ |
| Export-Download | ✅ |
| Studio-Demo-UI (studio-demo.php) | ✅ |
| Health-Check-Endpoint | ✅ |
| Render-Deployment + Persistent Disk | ✅ |
| Security: .htaccess, MIME-Check, escapeshellarg | ✅ |
| E2E-Test (alle 13 Checks grün) | ✅ |

---

## V0.2.0 — KI-Bildgenerierung 🟡 IN PROGRESS

**Ziel:** Echte KI-Bilder als Slot-Replacement über Kie.ai Flux Kontext.

| Feature | Status | Blocker |
|---|---|---|
| api/generate-ai.php (Task starten) | ✅ | — |
| api/ai-status.php (Pollen + Bild DL) | ✅ | — |
| Apache PassEnv KIE_AI_API_KEY | ✅ | — |
| KIE_AI_API_KEY in Render gesetzt | 🟡 | User muss Key eintragen |
| E2E-Test AI: Upload→Generate→Render | ⬜ | Key ausstehend |
| UI-Button "KI-Bild generieren" | ⬜ | Backend erst fertig testen |
| Polling-UI (Fortschrittsanzeige) | ⬜ | — |

---

## V0.3.0 — Audio + Stabilität ⬜ GEPLANT

| Feature | Beschreibung |
|---|---|
| Audio-Preservation | Original-Audio durch Render durchleiten (Concat-Homogenität lösen) |
| 1080p-Render | Starter+-Plan (mehr RAM) |
| Longer Videos | >15s, bis 60s |
| settings.php | App-Einstellungen UI (TODO #35) |
| Performance-Optimierung | FFmpeg-Parallelisierung, Thumbnail-Caching (TODO #36) |
| E2E Tests (Playwright) | Automatisierte Regression-Suite (TODO #39) |

---

## V1.0.0 — SaaS-Launch ⬜ GEPLANT

**Ziel:** Zahlende Kunden, echte Monetarisierung.

| Feature | Beschreibung |
|---|---|
| User-Auth (Login/Register) | Session-basiert oder OAuth |
| Kristalle-System | Interne Credits für KI-Generierungen |
| Stripe-Integration | Payment für Starter+ und Pro-Plan |
| Projekt-Persistenz | User sieht eigene Projekte nach Login |
| Template-Bibliothek | Vorlagen für häufige Szenen-Typen |
| Share-Funktion | Download + WhatsApp + YouTube-Upload |
| Email-Notifications | Render fertig, Low-Credits Warning |

---

## V2.0.0 — Scale ⬜ VISION

| Feature | Beschreibung |
|---|---|
| S3/R2-Storage | Cloudflare R2 statt Render Disk |
| Multi-User-Teams | Gemeinsame Projekte, Rollen |
| KI-Video-Slots | Kling/Sora2 für kurze KI-Video-Clips |
| API-Zugang | REST-API für externe Integrationen |
| White-Label | Anpassbare Branding-Optionen |
| Mobile App | PWA oder React Native |
| Analytics-Dashboard | Nutzungsstatistiken, Render-Zeiten |

---

## Offene TODOs (aus TODO.md)

| # | Aufgabe | Priorität | Status |
|---|---|---|---|
| S6 | scene-editor-test.html → IONOS pushen | P1 | 🟡 (User-Aktion) |
| S7 | Live-Test: Slot ersetzen + meta.json | P1 | ⬜ |
| S8 | get-job.php Frontend-Restore nach Reload | P2 | ⬜ |
| 35 | settings.php UI | P2 | ⬜ |
| 36 | Performance-Optimierung | P2 | ⬜ |
| 39 | E2E Tests Playwright | P3 | ⬜ |
| 40 | Installer / Release Notes | P3 | ⬜ |

---

## Council-Entscheidungen ausstehend

Folgende Fragen sollten vor Implementierung durch den LLM Council gegangen werden:

1. **Pricing-Modell:** Welcher Preis für Starter+? $7 vs $12 vs $19?
2. **Kristalle-System:** Eigene Währung vs. direkte Credit-Anzeige?
3. **Auth-Stack:** Session-basiert vs. OAuth (Google/GitHub)?
4. **V1-Launch-Scope:** Was ist das minimum für zahlende Kunden?
5. **Domain-Strategie:** cinematic-studio-family.com vs. cinematic-vision-studio.com?
