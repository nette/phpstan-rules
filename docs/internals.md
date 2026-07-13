# PHPStan-Rules internals

Custom PHPStan rules and dynamic-type extensions for the Nette libraries. ~25 of the
31 files are thin adapters over a PHPStan interface, and `AGENTS.md` already documents
each one-by-one — this file covers only the four things that are genuinely non-obvious
and cut across the per-directory layout. One file.

## The component-tree model (highest value)

`Components/ComponentTreeResolver` is **not** a PHPStan extension — it is a plain
shared service injected into **two** return-type extensions
(`ComponentModel\GetComponentReturnTypeExtension` for `Nette\ComponentModel\Container`
and `Forms\FormContainerReturnTypeExtension` for `Nette\Forms\Container`). That split
is the central seam.

It reconstructs the return type of `$this->getComponent('x')` / `$this['x']` — which
Nette resolves at runtime through `createComponent<Name>()` factories and `$container->
addXxx('name')` calls, neither expressible in PHPDoc — by **re-parsing PHP source and
walking the raw php-parser AST** (reflection can't see inside method bodies). Two
strategies combine: the **component-model** one reflects `createComponent<Name>()`'s
return type; the **forms** one scans the factory body for `$var->addXxx('name')` and
returns that method's declared type (always resolved against `Nette\Forms\Container`).

The taxonomy to keep is the **three entry points** matching three call syntaxes:
`$form['x']` on a local variable, `$expr['child']` on an expression, and the dash path
`$this['a-b-c']` (where **every prefix segment must be a `createComponent` factory**,
else it bails). The clever part is the **cross-method/cross-class trace**: if `addXxx`
isn't in the current body, it follows the variable's assignment back to a *source
factory method*, re-parses **that** method's file, finds its returned variable, and
recurses — bounded by a load-bearing **`depth = 3`** guard against infinite factory
chains. The AST walkers **deliberately do not descend into closures/arrow-fns/anon
classes** (a return inside a closure isn't the factory's return) — the same
closure-skip invariant recurs in `RethrowAbortExceptionRule`.

**The divergent-fallback trap.** Because `Forms\Container extends
ComponentModel\Container`, both extensions fire for a form and PHPStan unions their
non-null results. The asymmetry is deliberate: the ComponentModel version returns
`null` on a miss (keep the declared type), while the Forms version does
`$type ??= BaseControl` and **never returns null** — that fallback is what makes
`$form['x']` default to a control. Both re-implement the same dispatch and the same
`$throw`-argument nullability logic, so editing one dispatcher silently desyncs the
pair.

## The type-extension vocabulary

The canonical shape: `getClass()` names the receiver, `isMethodSupported()` gates by
method name, `getTypeFromMethodCall()` returns `?Type` where **`null` means "I decline,
keep the declared type."** Universal guards everywhere: bail on `isFirstClassCallable()`
and require exactly one `getConstantStrings()` for the name argument. Several PHPStan
interface variants are used (`DynamicMethodReturnTypeExtension`,
`DynamicStaticMethodReturnTypeExtension`, `MethodsClassReflectionExtension`,
`StaticMethodTypeSpecifyingExtension`, `ExpressionTypeResolverExtension`, …). The
trickiest, worth pointers rather than re-narration:

- **`Utils/StringsReturnTypeExtension`** — a fallback *lattice* when the pattern is
  non-constant: it hand-builds types from PREG flags (`buildElementType`,
  `buildListType` = `ArrayType ∩ AccessoryArrayListType`), and models `matchAll` lazily
  as a `GenericObjectType(Generator, …)` because the shape matcher can't. `match()` adds
  null; `matchAll()` never does.
- **`Utils/HtmlMethodsClassReflectionExtension`** — a *reflection* extension synthesizing
  `getXxx`/`setXxx`/`addXxx` magic methods on `Nette\Utils\Html` (they go through
  `__call`). `Html` is also a `universalObjectCratesClasses` entry — a second non-local
  touchpoint.
- **`Utils/ArraysInvokeTypeExtension`** — forwards args through
  `ParametersAcceptorSelector` to pick the right callable overload, `void`→`null`.

**Shared-helper seams are where drift bites:** `StringsRegexHelper` centralizes PREG
flag mapping for the three Strings extensions *and* `ValidRegularExpressionRule`; the
Assets families each have a shared resolver. And a **`Rule` receives raw,
non-normalized args** (unlike type extensions), so `ValidRegularExpressionRule` must
resolve the pattern by name-then-position (`StringsRegexHelper::findArg`) or a named-arg
reorder validates the wrong argument as a regex.

## The "meta" extension: `Php/RemoveFailingReturnTypeExtension`

It strips `|false` (or `|null` for `preg_*`) from ~150 native functions/methods whose
error value is trivial or outdated (a flat NEON allowlist). Architecturally it is an
**`ExpressionTypeResolverExtension`**, which runs **before** all dynamic extensions — so
to avoid clobbering its siblings it must **re-run the pipeline and then subtract**: for
each call it selects the parameters acceptor, normalizes arguments, **manually iterates
the `DynamicReturnTypeExtensionRegistry`** to let other extensions compute their type
first, unions them, and only then applies `removeFalse`/`removeNull`. This is the one
place the library reaches into PHPStan's internal registry. Two `preg_*` special cases:
skip a non-constant pattern, and skip a `//u` UTF-8-validation `preg_match` where `false`
genuinely means invalid UTF-8. Note that with the `u` modifier *any* `preg_*` call can
return `false` for an invalid UTF-8 subject; stripping it anyway is deliberate — a regex
call site should not double as input validation. The sanctioned pattern for untrusted
input is an explicit up-front `preg_match('##u', $s)` check, the one form where `false`
survives.

## The testing harness

There is exactly **one** test runner — `tests/extensions.phpt`, a flat script of
`TypeAssert::assert*()` calls; every other `tests/**/*.php` is a **data fixture**
(analyzed, not run). The harness is `src/Tester/TypeAssert` (built to be reused by other
Nette packages). Its non-local invariants are expensive to rediscover:

- Both the `pathRoutingParser` **and** `NodeScopeResolver` need `setAnalysedFiles([$file])`
  — without the *parser* call, `CleaningParser` strips function bodies and
  `ComponentTreeResolver`'s AST walk finds nothing.
- Containers are cached per config+file hash; the analyzed file is also added to
  `analysedPaths` so classes defined *in the fixture* are in reflection scope.
- **What runs vs not:** because these call `Analyser`/`NodeScopeResolver` directly,
  custom rules and `IgnoreErrorExtension`s run, but config-level `ignoreErrors` (the Forms
  event-handler suppression) runs in a higher pipeline layer and is **not** covered —
  the single biggest "verified only by real projects" gap.
- `AssertTypeNarrowingExtension` is a **shipped** type-specifying extension (not
  test-only) that maps `Tester\Assert::same/type/true/…` to `Identical`/`is_*()`/
  `Instanceof_` and delegates to `TypeSpecifier` — so fixtures get narrowing *and* it is a
  real Nette-Tester feature.

## Registration

Registration is entirely NEON, layered: `extension.neon` (entry) → `extension-php.neon`
(generic PHP) → `extension-nette.neon` (per-package, grouped by comment). **A class is
inert until tagged**; the shared services (`ComponentTreeResolver`, `StringsRegexHelper`,
`TableRowTypeResolver`, `MapperTypeResolver`) are **untagged**, wired by constructor
injection. Config-driven services carry their knowledge as NEON parameters with a
`parametersSchema` (asset mapping, the RemoveFailing allowlist).
