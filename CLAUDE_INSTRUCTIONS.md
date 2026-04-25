# CLAUDE_INSTRUCTIONS.md — Cinematic Studio Family

## Arbeitsregeln für Claude

---

## 1. Arbeitsprinzipien

- **Immer zuerst lesen:** PROJECT_STATUS.md, TODO.md, ARCHITECTURE.md — bevor Code gebaut wird
- **Nur am nächsten offenen Task arbeiten** (TODO.md, niedrigste offene Nummer mit höchster Priorität)
- **Nichts neu bauen, was bereits funktioniert**
- **Kein Over-Engineering:** Keine Abstraktionen, die nicht sofort gebraucht werden
- **Nach jedem Abschnitt stoppen und auf OK warten**
- **Tokenverbrauch klein halten:** Dateien nur lesen wenn nötig, keine unnötigen Explorationen

---

## 2. Workflow pro Session

```
1. PROJECT_STATUS.md lesen → aktuellen Stand verstehen
2. TODO.md lesen → nächsten offenen Task identifizieren
3. ARCHITECTURE.md lesen → technischen Kontext prüfen
4. Task umsetzen
5. Dokumentation aktualisieren:
   - TODO.md: Task als ✅ markieren
   - CHANGELOG.md: Änderung eintragen
   - PROJECT_STATUS.md: Status-Tabelle aktualisieren
6. Stoppen und auf OK warten
```

---

## 3. Code-Regeln

- **Sprache:** TypeScript (strict mode)
- **Keine Kommentare**, außer das WHY ist nicht offensichtlich
- **Keine `any`-Typen**
- **Keine halb-fertigen Implementierungen** — entweder vollständig oder nicht anfangen
- **Sicherheit:** Keine Command Injection, XSS, SQL Injection
- **Electron IPC:** Immer contextBridge + preload verwenden (kein `nodeIntegration: true`)

---

## 4. Datei-Konventionen

```
Komponenten:     PascalCase.tsx         (z.B. TimelineEditor.tsx)
Hooks:           use + camelCase.ts     (z.B. useMediaImport.ts)
Services:        camelCase.service.ts   (z.B. ffmpeg.service.ts)
Typen:           camelCase.types.ts     (z.B. project.types.ts)
Tests:           *.test.ts / *.spec.ts
```

---

## 5. Verbotene Aktionen (ohne explizites OK)

- ❌ Git push / force push
- ❌ Dateien löschen
- ❌ Abhängigkeiten downgraden
- ❌ Bestehenden, funktionierenden Code refactorn
- ❌ Neue Features außerhalb des aktuellen Tasks
- ❌ Automatisch weiterbauen nach Abschluss eines Tasks

---

## 6. Dokumentations-Pflicht

Nach **jedem** abgeschlossenen Task:
- [ ] TODO.md aktualisieren
- [ ] CHANGELOG.md Eintrag hinzufügen
- [ ] PROJECT_STATUS.md Status-Tabelle prüfen
- [ ] ARCHITECTURE.md bei strukturellen Änderungen aktualisieren

---

## 7. Bei Unklarheiten

Erst fragen, dann bauen. Lieber eine kurze Rückfrage als die falsche Implementierung.
