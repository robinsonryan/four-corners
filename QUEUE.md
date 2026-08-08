# Implementation Queue

> Deferred work, captured mid-session, picked up deliberately. Managed by `/queue`;
> convention: `$CLAUDE_HARNESS_DIR/notes/implementation-queue.md`. Hand-editing is fine.

## Queued
### `DocumentAnnotator` declares a `cancel` event that can never fire
- **Added**: 2026-08-08 · Track P frontend gating
- **Tier**: SOLO (needs a UI decision first)
- **Context**: `defineEmits` declares `cancel: []` and `handleCancel()` exists, but no control in the template invokes it. Consumers can listen for `@cancel` and will never receive it. Either wire a cancel control or drop the event — deleting the handler alone would leave a declared public event with no emitter. The handler carries an `eslint-disable` pointing here.

### `npm run build` removed — this package ships source, not a bundle
- **Added**: 2026-08-08 · Track P frontend gating
- **Tier**: none (recorded, not deferred work)
- **Context**: the `build` script ran `vite build` with **no `vite.config.ts` in the package**, so it always failed with "Could not resolve entry module index.html". It had never worked. `main` points at `resources/js/index.ts` and `types` at raw `.ts`, and consumers compile the source in their own build, so there is no artifact to produce. The script was removed rather than repaired. If this package ever needs to ship a bundle, add a Vite lib-mode config and put `build` back in `quality`.


### Support Pest 5 / PHPUnit 13 / PHP 8.4+ in the constraint matrix
- **Added**: 2026-08-07 · harness health & efficiency session — apps are queued to upgrade to Pest 5 for Tia; consuming apps can't move until this package allows it
- **Tier**: SOLO
- **Why deferred**: harness-wide decision made first; per-package constraint widening is independent work
- **Context**: current: pest ^4.0. Widen composer constraints to include pest ^5 / phpunit ^13 and run the suite on the new matrix. Research + decisions: $CLAUDE_HARNESS_DIR/notes/harness-health-research-2026-08.md. **The php half of this item is done** — the floor moved to `^8.2` (harness decision D-C) on 2026-08-07, so 8.3/8.4/8.5 are all already permitted; only the pest/phpunit widening remains

### Frontend quality gate (ESLint + typecheck + vitest)
- **Added**: 2026-08-07 · package-quality-baseline P4 (php half)
- **Tier**: SOLO
- **Why deferred**: tracked as spec item **P7a**, a separate ledger item; the P4 pass was scoped to the PHP half only and deliberately did not touch `package.json`
- **Context**: observed while gating the php half, not fixed — there is **no ESLint config at all**, and `package.json` has no `lint`/`lint:check` script and no composed `quality` script for the js half. Dev deps are a major behind across the board: vite `^6`, vitest `^2`, vue-tsc `^2`, happy-dom `^15`, @types/node `^22`. `resources/js/vendor/opencv.js` is a vendored third-party blob and will need an ignore entry once ESLint lands, or it will bury the real findings

### PHP analysis does not cover `database/`, `routes/`, `config/`
- **Added**: 2026-08-07 · package-quality-baseline P4
- **Tier**: SOLO
- **Why deferred**: `paths: [src]` is the canonical baseline the sweep asserts across all seven packages; widening it is a harness-wide convention change, not a four-corners decision
- **Context**: PHPStan analyses `src` only (39 files) and Rector covers `src` + `tests`. `database/seeders/*` and `database/migrations/*` carry real logic — including the uuid7/bigint id-type branching — and are analysed by nothing. Raise with the harness before widening so all packages move together

## Blocked

## Archive
