# Changelog

Notable changes to the pacurar2020 theme. Dates use Europe/Bucharest.

## 2026-10-01 — Health baselines

### Added
- **Personal baselines on the “At a glance” cards.** With at least 3 readings in
  the last 7 days and 14 in the 53 days before them, a card shows its 7-day
  average against your normal range (that period’s mean ± one standard
  deviation) and an “Above / Within / Below your normal” status. With less
  history it falls back to the change since the previous reading.
- **Good/bad direction for metrics.** 67 entries in `app/Health/metrics.json`
  now carry `"better": "up"` or `"down"`. Overview cards and the per-entry
  “vs previous day / vs 7 days earlier” deltas are coloured accordingly;
  metrics without a clear direction stay neutral.
- **Stale-reading notice.** A card whose latest reading is more than 2 days old
  is dimmed and says “No new reading for N days”.
- **`skipped_metrics` in the health-entries API response**, listing metric keys
  the blog’s catalog does not recognise.

### Changed
- **Unknown metrics no longer reject a whole entry.** `EntryContract` drops only
  the unrecognised metric keys (including inside workouts). Entries with a known
  key under the wrong source or topic, or with nothing valid left, are still
  rejected.
- **HRV fields are split by method (breaking for API/MCP consumers).**
  `heart.hrv_ms` is now Apple Health SDNN only. Oura’s `oura.sleep.average_hrv`
  (RMSSD) moved to `recovery.hrv_ms`, alongside Recovery HRV. Queries for Oura
  HRV must use `recovery.hrv_ms`. Docs and tests updated.

### Fixed
- Unbalanced `<span>` in the overview card markup: its closing tag was inside a
  Blade comment.

## 2026-10-01 — Recovery HRV (commit 1367623)

### Added
- Apple Health metric `heart_rate_variability_rmssd`, labelled **Recovery HRV**
  (ms), exposed as `recovery.hrv_ms`.
- Recovery HRV is the featured number on heart entries and has its own “HRV”
  card in the overview; the overview grid is now 5 columns on desktop.
