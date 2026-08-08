# FourCorners

Document annotation for Laravel: a person drags four corner handles onto a photographed
document, and the package records the corner positions, the perspective-corrected output,
and the delta between the machine's guess and the human's answer. That delta is the point —
it exports as ML training data for a future auto-detection model.

Composer name: `robinsonryan/four-corners` — a **library**, not an application.

## Conventions

@import ./constitution.md
@import ./imports/package-conventions.md
@import ./imports/package-quality-gate.md
@import ./imports/testing-conventions.md
@import ./imports/php-conventions.md
@import ./imports/git-conventions.md
@import ./imports/frontend-conventions.md

> `frontend-conventions.md` is imported because this package **has a frontend half**
> (Vue 3 components + Vitest). The other inherited app conventions in `.claude/imports/`
> — `authorization-conventions.md`, `pwa-conventions.md`, `ddev-worktrees.md` — are
> deliberately **not** imported: they describe Inertia `can` maps, app-shaped Vite wiring,
> and nested app worktrees, none of which exist in a package. Read them if a question
> genuinely calls for one; do not load them by default.

> `.claude/` is a set of **harness symlinks** and is gitignored — a fresh clone has
> none of them and the `@import`s above resolve to nothing. If a convention file is
> missing, restore the link rather than guessing:
> `~/workspace/harness/link.sh project laravel-package $(pwd)`

## The gate

`ddev composer quality` — `lint:check` → `analyze` → `refactor:check` → `test`.
Verify-only: it never rewrites files. Fix with `ddev composer lint` /
`ddev composer refactor` and re-stage.

`.githooks/pre-commit` runs **the whole gate, tests included** — packages are
small enough (13–21 s measured) that the apps' exclude-the-tests compromise does
not apply. It is path-aware, so a docs-only commit skips it. Never bypass with
`--no-verify`; `PACKAGE_SKIP_GATE=1` is a human emergency valve and **agents must
never set it**.

That hook file is a **copy** of the harness's canonical one. Do not edit it here
— edit `$CLAUDE_HARNESS_DIR/core/stacks/laravel-package/hooks/pre-commit` and
re-run that directory's `install.sh`.

`harness package-check` sweeps every first-party package: the gate, a
`--prefer-lowest` run proving the declared version floor really resolves,
outdated and vulnerability scans, and in-constraint updates behind a re-run of
the gate. It never tags a release. Run it before any app re-resolves its
packages.

Full definition: `imports/package-quality-gate.md`. Skill: `/package-quality`.

### The frontend gate

`ddev exec npm run quality` — `lint:check` (ESLint, `--max-warnings=0`) →
`typecheck` (`vue-tsc --noEmit`) → `test` (Vitest). The pre-commit hook runs it on any
commit that stages `.ts` / `.vue` / `.js` / `.css` or a frontend config.

**There is deliberately no `build` step, and there is no `vite.config.ts`.** Do not add
one "to complete the set." The old `build` script ran `vite build` with no Vite config at
all and had never once succeeded — it always died on "Could not resolve entry module
index.html". This package ships **source**: `package.json` `main` points at
`resources/js/index.ts` and `types` at raw `.ts`, and the consuming app compiles those in
its own build. There is no artifact to produce. If that ever changes, it needs a real Vite
lib-mode config first, and only then does `build` go back into `quality`.

ESLint ignores `resources/js/vendor/**` (the vendored OpenCV blob) and `docs/**` (the
README example imports through a host-app alias that cannot resolve here). Two rules that
catch people out, both from tseslint's strict preset: no non-null assertion `!`, and
`readonly T[]` rather than `ReadonlyArray<T>`.

## Lock files are tracked

Unlike most sibling packages, this repo commits **both `composer.lock` and
`package-lock.json`**. A dependency refresh therefore produces a real, committable diff —
stage it with the `composer.json` / `package.json` change that caused it.

## The two halves and where they meet

**All image processing happens in the browser.** PHP never touches pixels. The Vue side
runs OpenCV.js, produces two base64 JPEGs (a display-size one and an archive-size one),
and posts them; the PHP side records metadata and hands the images straight to the
consuming app through an event. The package stores no files.

### PHP surface

| Entry point | What it is |
|---|---|
| `Facades\FourCorners` → `Services\AnnotationService` | `start`, `complete`, `reject`, `find`, `getPending`, `getMetricsSummary`. The whole public API |
| `Events\*` | The integration seam. `AnnotationCompleted` carries `$annotation` plus **both base64 images** — the consuming app subscribes and decides where they land. Also `AnnotationStarted`, `AnnotationRejected`, `CornersAdjusted`, `TrainingDataExported` |
| `routes/four-corners.php` | Group under `four_corners.routes.prefix` (default `admin/annotations`, middleware `['web','auth']`, name prefix `four-corners.`): `config`, `start`, `show`, `complete`, `reject`, plus `demo`/`test` pages. Handled by `Http\Controllers\AnnotationController` |
| `Models\DocumentAnnotation`, `DocumentType`, `RejectionReason` | `DocumentType` carries aspect ratio + display/archive output sizes; `RejectionReason` is the categorized why-this-image-is-unusable list |
| `Services\MetricsCalculator` | Per-corner pixel distance between the auto-detected guess and the human's final corners — the signal the whole package exists to collect |
| `Services\TrainingDataExporter` + `ExportTrainingDataCommand` | JSONL export for model training |
| `Contracts\AnnotationRepositoryInterface` | Bound to `Repositories\EloquentAnnotationRepository` in the service provider. Swap it to change persistence |

Identifiers and tables are configurable, and both are load-bearing:
`Concerns\ConfiguresIdentifiers` reads `four_corners.id_type` (`incrementing` or `uuid7`;
**config default is `uuid7`**) and drives `getIncrementing()`, `getKeyType()`, and a
`creating` hook that stamps `Str::uuid7()`. `Support\TablePrefixer` applies
`four_corners.table_prefix` to every table name. The migrations branch on `id_type` for
the *user-id* columns too (`annotated_by`, the tenant column) — they become `uuid` columns
in uuid7 mode. Anything touching those columns must stay `int|string`, never `int`.

### Vue surface

`resources/js/index.ts` is the package's JS entry and exports exactly:

- **Components** — `DocumentAnnotator` (the one consumers actually mount), plus its parts:
  `CornerHandle`, `QuadrilateralOverlay`, `RotationControls`, `ZoomControls`,
  `ActionButtons`, `RejectModal`, `PreviewModal`.
- **Composables** — `useOpenCV`, `useCornerDetection`, `usePerspectiveTransform`,
  `useAnnotationState`, `useExifOrientation`.
- **Types** — everything in `Types/index.ts`.

Canvas rendering is Konva (`konva` + `vue-konva`): a `v-stage` / `v-layer` holding the
image, the quadrilateral overlay, and four draggable corner handles.

`DocumentAnnotator` emits `complete` (a `CompletePayload`: annotation id, final corners,
final rotation, seconds spent, both base64 images, and the original auto-detection so the
delta can be computed) and `reject`. Those payloads are what the `complete` / `reject`
routes consume — that is the entire seam between the halves.

## OpenCV interop

OpenCV.js is a ~9.8 MB emscripten build vendored at `resources/js/vendor/opencv.js`. It
ships **no types**, so the interop is declared by hand in `resources/js/Types/opencv.ts` —
deliberately only the ~30 members this package actually calls (`Mat`, `MatVector`, `Size`,
the constants, `imread`/`imshow`, the Canny/contour chain, `getPerspectiveTransform`,
`warpPerspective`, `rotate`). It is not an attempt to describe OpenCV. **If you call a new
`cv.*` member, add it to that file rather than reaching for `any`** — the file exists
precisely because the composables used to be a wall of `any`.

Two consequences to hold onto:

- `CvMat` and `CvMatVector` expose `delete()`. That is emscripten heap memory, not
  garbage-collected. Every matrix you allocate must be deleted or the tab leaks.
- `cv` is a **global**, not an import. `useOpenCV.ts` declares it via `declare global`, and
  `eslint.config.js` lists `cv: "readonly"` so lint agrees. `useOpenCV(url)` injects the
  script tag, waits for `onRuntimeInitialized`, caches the instance, and times out at 30 s.

Where the script comes from is configurable and has **two sources that can disagree**:
`config/four-corners.php` `opencv_url` defaults to the public CDN
(`https://docs.opencv.org/4.9.0/opencv.js`), while the package also serves its own vendored
copy from the unauthenticated route `four-corners/opencv.js`. Point
`FOUR_CORNERS_OPENCV_URL` at that route to run offline or to pin the exact build.

## Testing

Pest + Orchestra Testbench. **This package is the exception to the stack's real-Postgres
rule** — `tests/TestCase.php` configures SQLite `:memory:` and works fine, because nothing
here depends on database-side `uuidv7()` defaults (ids are stamped in PHP by
`ConfiguresIdentifiers`). Don't "fix" it toward Postgres without a reason.

`TestCase` also forces `id_type` to `uuid7` and strips `auth` from the route middleware, so
HTTP tests hit the controllers directly. Spatie Laravel Data's full config is inlined there
because Testbench does not load the package's own config.

```bash
ddev composer test
ddev exec vendor/bin/pest --filter=SomeTest
ddev exec npm run test          # Vitest, resources/js/**/*.spec.ts
```

There is no `ddev artisan` and no `ddev pest` here — those are app commands.

## Gotchas

- **Static analysis covers `src` only.** PHPStan is `paths: [src]`, level 8, pinned to
  `phpVersion: 80200` so the declared PHP floor is checked mechanically. Rector covers
  `src` + `tests`. Nothing analyses `database/`, `routes/` or `config/` — and the seeders
  and migrations carry the real id-type branching. Widening it is a harness-wide decision,
  not a four-corners one (see `QUEUE.md`).
- **`DocumentAnnotator` declares a `cancel` event that can never fire.** `defineEmits` has
  `cancel: []` and `handleCancel()` exists, but no control in the template invokes it.
  Deleting the handler alone would leave a declared public event with no emitter — it needs
  a UI decision. Tracked in `QUEUE.md`; the handler carries an `eslint-disable` pointing
  there.
- **`Contracts\ImageProcessorInterface` is declared and never implemented or bound.** It is
  a placeholder for server-side ML detection. Don't assume something fulfils it.
- **Supported Laravel is `^12.0|^13.0`.** `^11.0` was dropped 2026-08-08: Pest 4 needs
  PHPUnit 12, Testbench 9 (which *is* Laravel 11) caps at PHPUnit 11, so a Laravel 11
  harness could never resolve here and that support was never once verified. Do not widen
  it back without a harness that can actually run it.

## Releases

**Never tag.** Automation may update, gate, commit and push a branch, then report
"ready to tag" with a suggested version. Ryan cuts every tag. A version number is
a claim about behavior that a green gate cannot substantiate.

This package is on `0.x` on purpose — `^0.4.0` resolves to `>=0.4.0 <0.5.0`, so every
minor may break, which is the honest signal while the API settles. Behavior changes land in
`CHANGELOG.md` in the commit that makes them.

## Reference package

`~/dev/php/packages/robinsonryan/hey-you/` is the reference implementation —
service provider shape, Testbench setup, tool configs, table prefixing. Read it
before inventing a variant.

## Quick reference

- **DDEV**: `ddev start`, `ddev ssh`
- **Gate**: `ddev composer quality` · **Frontend gate**: `ddev exec npm run quality`
- **Tests**: `ddev composer test` · **JS tests**: `ddev exec npm run test`
- **Style fix**: `ddev composer lint` · **JS style fix**: `ddev exec npm run lint`
- **Rector fix**: `ddev composer refactor`
- **Docs**: `docs/installation.md`, `docs/configuration.md`, `docs/usage.md`,
  `docs/examples/AnnotatePage.vue`
