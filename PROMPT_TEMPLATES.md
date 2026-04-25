# PROMPT_TEMPLATES.md — Cinematic Studio Family

Wiederverwendbare Prompt-Vorlagen für effiziente Claude-Sessions.

---

## T01 — Session starten

```
Lies zuerst:
- PROJECT_STATUS.md
- TODO.md
- ARCHITECTURE.md

Arbeite nur am nächsten offenen Task (niedrigste Nummer, höchste Priorität).
Baue nichts neu, was bereits funktioniert.
Halte den Tokenverbrauch klein.
Nach Abschluss: Dokumentation aktualisieren, dann auf mein OK warten.
```

---

## T02 — Neuen Task beginnen

```
Lies PROJECT_STATUS.md und TODO.md.
Nächster offener Task: [TASK-NUMMER] — [TASK-NAME]
Setze nur diesen Task um.
Kein Over-Engineering, keine ungeplanten Extras.
Stopp nach Fertigstellung.
```

---

## T03 — Bug fixen

```
Bug: [BESCHREIBUNG]
Betroffene Datei(en): [PFAD]
Fehlermeldung: [FEHLER]

Lies nur die betroffenen Dateien.
Fix den Bug minimal — kein Refactoring drumherum.
Erkläre kurz die Ursache.
Aktualisiere CHANGELOG.md danach.
```

---

## T04 — Feature erweitern

```
Bestehendes Feature: [NAME]
Erweiterung: [WAS GENAU]

Lies zuerst die betreffenden Dateien.
Erweitere nur das Beschriebene — nichts Zusätzliches.
Kein Umbau funktionierender Teile.
```

---

## T05 — Architektur-Entscheidung

```
Entscheidung benötigt: [THEMA]
Optionen: [A] vs [B]
Kontext: [KURZBESCHREIBUNG]

Gib eine Empfehlung mit dem wichtigsten Trade-off.
Maximal 3 Sätze.
Kein Code, nur Entscheidungsgrundlage.
```

---

## T06 — Dokumentation aktualisieren

```
Task abgeschlossen: [TASK-NUMMER] — [TASK-NAME]
Was wurde gebaut: [KURZBESCHREIBUNG]

Aktualisiere:
1. TODO.md → Task als ✅ markieren
2. CHANGELOG.md → Eintrag hinzufügen
3. PROJECT_STATUS.md → Status-Tabelle prüfen
4. ARCHITECTURE.md → falls strukturelle Änderungen

Kein Code ändern.
```

---

## T07 — Code Review

```
Datei: [PFAD]
Prüfe auf:
- TypeScript Fehler / any-Typen
- Sicherheitslücken (XSS, Injection)
- Unnötige Komplexität
- Fehlende Fehlerbehandlung an System-Grenzen

Nur Befunde auflisten — kein automatisches Fixen.
```

---

## T08 — Export / Release vorbereiten

```
Lies PROJECT_STATUS.md und CHANGELOG.md.
Prüfe: Sind alle P0/P1 Tasks in TODO.md abgeschlossen?
Erstelle Release-Notizen für Version [X.Y.Z].
Kein Code ändern.
```

---

## T09 — Technologie-Stack festlegen

```
Projekt: Cinematic Studio Family
Offene Entscheidung: [THEMA aus ARCHITECTURE.md]

Kontext:
- Desktop-App für Familien (nicht-technische Nutzer)
- Video-Verarbeitung mit FFmpeg
- Cross-Platform: Windows, macOS, Linux

Empfehlung mit einem Satz Begründung.
Danach ARCHITECTURE.md entsprechend aktualisieren.
```

---

## T10 — Session beenden

```
Aktuelle Session abschließen.
Prüfe: Sind alle Dokumentations-Updates gemacht?
- TODO.md ✓
- CHANGELOG.md ✓  
- PROJECT_STATUS.md ✓

Kurze Zusammenfassung: Was wurde erledigt, was ist der nächste offene Task.
```
