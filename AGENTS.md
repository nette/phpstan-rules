# To My Agents!

It is my fervent wish that this file guide every AI coding agent working with code in this repository.

## Documentation

Any distilled, agent-facing documentation for this package - how it works
internally and the rationale behind key design decisions - lives in `docs/`.
Consult it before non-trivial changes; it is the source of truth from which the
public manual is distilled.

- **`docs/reference.md`** - a catalog of every rule and type extension (interface
  type, what it does, config file). Consult it to find an existing extension.
- **`docs/internals.md`** - the four cross-cutting mechanisms that the per-extension
  catalog fragments: the component-tree resolver, the type-extension vocabulary,
  `RemoveFailingReturnType`, and the test harness. Read it before non-trivial edits.

## Project Overview

`nette/phpstan-rules` is a PHPStan extension package for Nette library developers:
custom rules and dynamic-type extensions applied when analysing Nette libraries.
Consumed by individual Nette repos via their PHPStan configuration.

- **PHP Version**: 8.2 - 8.5
- **Package**: `nette/phpstan-rules` (namespace `Nette\PHPStan\`)

## Essential Commands

```bash
# Static analysis (self, level 8) and tests
composer phpstan
composer tester
vendor/bin/tester tests/extensions.phpt -s
```

## Architecture

- **`src/`** (PSR-4 `Nette\PHPStan\`); per-package extensions use dedicated
  sub-namespaces (`Nette\PHPStan\ComponentModel\`, `\Schema\`, `\Utils\`,
  `\Application\`, ...), generic PHP-level ones use `Nette\PHPStan\Php\`.
- **Registration is layered NEON:** `extension.neon` (entry, auto-included by
  `phpstan/extension-installer`) → `extension-php.neon` (generic PHP) →
  `extension-nette.neon` (per-package, grouped by comment). **A class is inert until
  tagged** (`phpstan.rules.rule`, `phpstan.broker.dynamic*ReturnTypeExtension`,
  `phpstan.ignoreErrorExtension`, `phpstan.broker.*ClassReflectionExtension`, etc.);
  shared services (`ComponentTreeResolver`, `StringsRegexHelper`, `TableRowTypeResolver`,
  `MapperTypeResolver`) are **untagged**, wired by constructor injection.
- **`src/Tester/TypeAssert.php`** is a reusable type-inference test helper used by
  other Nette packages too.

## Working in this repo

- **~25 of the ~31 files are thin adapters over a PHPStan interface** - documented
  one-by-one in `docs/reference.md`. The real depth is the four cross-cutting things
  in `docs/internals.md`:
  - **The component-tree model** is the highest-value seam: `ComponentTreeResolver`
    (an untagged shared service) is injected into **two** return-type extensions
    (ComponentModel and Forms) that re-parse PHP source and walk the AST with a
    `depth = 3` guard. They share dispatch logic but have a **divergent fallback**
    (Forms never returns null → `BaseControl`; ComponentModel returns null) - editing
    one dispatcher silently desyncs the pair.
  - **Type-extension vocabulary:** `getTypeFromMethodCall()` returning `null` means
    "decline, keep the declared type"; universal guards bail on
    `isFirstClassCallable()` and require one `getConstantStrings()` for the name.
  - **A `Rule` receives raw, non-normalized args** (unlike type extensions), so
    `ValidRegularExpressionRule` must resolve the pattern by name-then-position.
  - **The test harness** needs `setAnalysedFiles()` on *both* the parser and
    `NodeScopeResolver`; config-level `ignoreErrors` runs above `Analyser` and is
    **not** covered by `TypeAssert` (verified only by real projects).
- **When you add a new extension:** (1) write a `.phpt` test in `tests/` with data
  files in `tests/data/` and run `composer tester`; (2) add a `###` section to
  `docs/reference.md`; (3) add it to `readme.md`.
