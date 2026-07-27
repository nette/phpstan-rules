# Extension reference

A catalog of every rule and type extension in this package, one entry each: its
PHPStan interface type, what it does, and which NEON file registers it. The
cross-cutting mechanics (the component-tree resolver, the type-extension vocabulary,
`RemoveFailingReturnType`, and the test harness) are in [internals.md](internals.md).

When you add a new extension, add a section here (see AGENTS.md workflow).

## PHP-level extensions (`extension-php.neon`)

### ClosureTypeCheckIgnoreExtension

`IgnoreErrorExtension`. Suppresses `expr.resultUnused` for the runtime type-validation
pattern `(function(Type ...$p) {})(...$args)`.

### Interface `@property` tag support

PHPStan core ignores `@property`/`@property-read` tags on interfaces:
`ClassReflection::hasProperty()` consults `PropertiesClassReflectionExtension`s only when
`allowsDynamicProperties()` is true, which on PHP 8.2+ requires `__get`/`__set`/`__isset` -
so a plain interface (e.g. `Nette\Assets\Asset` with `@property-read string $url`) yields
`property.notFound` and a custom properties extension is never even called. The workaround
is a pair of extensions sharing `InterfacePropertyTagResolver` (a plain service):

- `InterfacePropertyTagTypeExtension` (`ExpressionTypeResolverExtension`) resolves the type
  of a `PropertyFetch` whose var type's class reflections all lack the native property but
  declare (directly or via an implemented/extended interface) a readable `@property` tag -
  returns the union of tag types. Bails when `$scope->hasExpressionType($expr)` is yes, so a
  narrowed type (e.g. after `if ($x->file !== null)`) wins; also bails when any reflection
  resolves the property natively. Nullsafe reads are covered automatically.
- `InterfacePropertyTagIgnoreExtension` (`IgnoreErrorExtension`) suppresses `property.notFound`
  on `PropertyFetch`/`NullsafePropertyFetch` nodes for which the resolver finds a tag type.
  Writes (virtual `PropertyAssignNode`) are deliberately not suppressed - the tags are read
  contracts.

Motivated by nette/assets but registered as generic PHP-level behaviour.

### RemoveFailingReturnTypeExtension

`ExpressionTypeResolverExtension`. Removes `|false` (or `|null` for `preg_*`) from return
types of native PHP functions/methods whose error return value is trivial or outdated.
Handles `FuncCall`/`MethodCall`/`StaticCall` in one class; flat NEON allowlist (plain names
for functions, `Class::method` for methods). Runs before all `DynamicReturnTypeExtension`s,
delegates to them via `DynamicReturnTypeExtensionRegistry`, then strips `|false`. For
`preg_replace`/`preg_replace_callback`/`preg_replace_callback_array`/`preg_filter` it strips
`|null` (null on PCRE error). See internals for the re-run-then-subtract detail and the
`//u` UTF-8 special case.

## Nette-package extensions (`extension-nette.neon`)

### ExpectArrayReturnTypeExtension

`DynamicStaticMethodReturnTypeExtension`. Narrows `Expect::array()` from `Structure|Type`:
no arg / null / empty array / non-Schema values → `Type`; all values implement `Schema` →
`Structure`; mixed/unknown → declared union.

### ArrowFunctionVoidIgnoreExtension

`IgnoreErrorExtension`. Suppresses `argument.type` when an arrow function (always returns a
value) is passed to a parameter typed `Closure(): void`. Affected functions/methods are a
flat NEON list (plain names for functions like `testException`, `Class::method` for methods
like `Tester\Assert::exception`).

### FalseToNullReturnTypeExtension

`DynamicStaticMethodReturnTypeExtension`. Narrows `Helpers::falseToNull()` from `mixed`:
removes `false`, adds `null` (`string|false` → `string|null`, `false` → `null`).

### StringsRegexHelper (Utils, shared service)

Consolidates the PREG regex logic shared by the Strings extensions to stop flag mapping
drifting. Instance, matcher-backed: `matchShape()` (for `match()`), `matchAllShape()` (for
`matchAll()`) - build the PREG flag mask and call `RegexArrayShapeMatcher`. `matchShape()`
passes `wasMatched = Yes` (shape of a successful match; the caller adds `|null` for the
no-match case), `matchAllShape()` passes `Maybe` (the result may be an empty list; `Yes`
would make PHPStan infer `non-empty-list`). Injected into `StringsReturnTypeExtension` and
`StringsReplaceClosureTypeExtension`. Static, stateless: `resolveFlag()` (boolean arg by
name/position), `findArg()` (arg by name or index) - used by `ValidRegularExpressionRule`
without pulling in the matcher. `StringsMatchTypeSpecifyingExtension` injects
`RegexArrayShapeMatcher` directly.

### StringsReturnTypeExtension

`DynamicStaticMethodReturnTypeExtension` (uses `StringsRegexHelper`). Narrows `Strings::match()`,
`matchAll()`, `split()`. With a constant pattern, derives the exact array shape from the regex
(capture/named/optional groups). Adds `null` for `match()`; `matchAll()` returns a list/array
without null. Non-constant pattern, helper-null, or the lazy `matchAll()` Generator → generic
shape from the boolean args (`captureOffset`, `unmatchedAsNull`, `patternOrder`, `lazy`).
`split()` always generic.

### StringsReplaceClosureTypeExtension

`StaticMethodParameterClosureTypeExtension`. Infers the `$replacement` callback parameter type
of `Strings::replace()` from the constant regex so the callback's `$matches` gets the exact
capture-group shape. Resolves pattern (index 1) and flags `captureOffset` (4)/`unmatchedAsNull`
(5); returns `Closure(<shape>): string`. Falls back to null when pattern/flags aren't constant.
Uses internal `NativeParameterReflection` (`phpstanApi.constructor` ignored for the file).

### StringsMatchTypeSpecifyingExtension

`StaticMethodTypeSpecifyingExtension` + `TypeSpecifierAwareExtension`. Narrows the **subject**
string after a truthy `Strings::match()`/`matchAll()` - e.g. inside `if (Strings::match($s,
'#\d+#'))`, `$s` becomes `non-empty-string` (`#^foo$#` → `non-falsy-string`). Only truthy
context; subject (index 0) must already be a string; patterns that can match empty (`#.*#`)
yield no narrowing.

### ArraysInvokeTypeExtension

`DynamicStaticMethodReturnTypeExtension`. Narrows `Arrays::invoke()`/`invokeMethod()` from
`array`. `invoke()`: callable return type from the iterable value type, forwards `...$args`
via `ParametersAcceptorSelector::selectFromArgs()`. `invokeMethod()`: resolves constant method
names on the object type. `callable(): void` → null. Falls back to declared type otherwise.

### CallbackToReflectionIgnoreExtension

`IgnoreErrorExtension`. Suppresses `argument.type` on the arg of `Nette\Utils\Callback::toReflection()`
(native param `mixed`, phpDoc `@param callable`, validated at runtime). Narrow match: identifier
`argument.type` + `StaticCall` `toReflection` on a `Nette\Utils\Callback` caller type. Only
`toReflection()` qualifies. Tested via `TypeAssert::assertNoErrors()`.

### HtmlMethodsClassReflectionExtension

`MethodsClassReflectionExtension`. Resolves `getXxx()`/`setXxx()`/`addXxx()` magic methods on
`Nette\Utils\Html` (via `__call`, not `@method`). `getXxx()` → `mixed`; `setXxx()`/`addXxx()`
→ `static`.

### GetComponentReturnTypeExtension

`DynamicMethodReturnTypeExtension`. Narrows `Container::getComponent()`/`offsetGet()`
(`$this['xxx']`). With a constant name, finds a `createComponent<Name>()` factory on the caller
and returns its type - `$this->getComponent('poll')` → `PollControl` if
`createComponentPoll(): PollControl` exists. Uses the shared `ComponentTreeResolver`
(see internals).

### FormContainerReturnTypeExtension

`DynamicMethodReturnTypeExtension`. Narrows `Forms\Container::getComponent()`/`offsetGet()`
(`$form['xxx']`) from `addXxx()` calls in the same function body - `$form['name']` → `TextInput`
after `$form->addText('name', ...)`. Falls back to `createComponent*()` lookup. Only simple
variable names. Uses `ComponentTreeResolver`; note the divergent-fallback trap with
`GetComponentReturnTypeExtension` (see internals).

### Form event-handler callback suppression (ignoreErrors, not a PHP extension)

Declarative `ignoreErrors` entry suppressing `assign.propertyType` on Form event-handler
properties (`Form::$onSuccess`/`$onError`/`$onSubmit`/`$onRender`, `Container::$onValidate`,
`SubmitButton::$onClick`/`$onInvalidClick`). Runtime reads the callback's data-parameter type
via `Callback::toReflection` and coerces values, so a narrower data param is valid. Gated on
the value containing `Closure(`/`callable(`; `reportUnmatched: false`. Not covered by
`TypeAssert` (config-level ignoreErrors runs above `Analyser`) - verified by real projects.

### AssertTypeNarrowingExtension

`StaticMethodTypeSpecifyingExtension` + `TypeSpecifierAwareExtension`. Narrows variable types
after `Tester\Assert` calls by mapping each assertion to an equivalent expression and delegating
to `TypeSpecifier::specifyTypesInCondition()`. Supports `null`, `notNull`, `true`, `false`,
`truthy`, `falsey`, `same`, `notSame`, `type` (built-in type strings and class/interface names).
A shipped feature, not test-only.

### Assets family (`MapperTypeResolver` shared service)

`MapperTypeResolver` resolves mapper IDs → mapper class types from a `mapping` config
(`'file'` → `FilesystemMapper`, `'vite'` → `ViteMapper`, or FQCN), resolves asset references
→ asset class types by file extension (mirroring `Helpers::createAssetFromUrl()`), parses
`'mapper:reference'`. Config parameter `nette.assets.mapping`.

- `GetMapperReturnTypeExtension` (`DynamicMethodReturnTypeExtension`) - `Registry::getMapper()`
  → specific mapper class; no arg → `'default'`.
- `MapperGetAssetExtension` (`DynamicMethodReturnTypeExtension`) - `FilesystemMapper::getAsset()`
  / `ViteMapper::getAsset()` → specific asset class by extension. One class registered twice
  with different `className`. ViteMapper `.js` → `ScriptAsset` (safe: `EntryAsset extends
  ScriptAsset`).
- `RegistryGetAssetExtension` (`DynamicMethodReturnTypeExtension`) - `Registry::getAsset()`/
  `tryGetAsset()` → specific asset class; parses the qualified reference; `tryGetAsset()` adds
  `|null`. String refs only.

### InjectPropertyExtension

`ReadWritePropertiesExtension`. Treats `#[Nette\DI\Attributes\Inject]` properties as
always-written and initialized (`isAlwaysWritten()`/`isInitialized()` → true, suppressing
`never written`/`uninitialized`); `isAlwaysRead()` stays false. Matches the attribute FQCN as a
string (no compile-time nette/di dependency). Only `#[Inject]`, not the legacy `@inject`.

### ValidRegularExpressionRule

`Rule<StaticCall>`, tag `phpstan.rules.rule` - the first custom **rule**. Reports invalid regex
patterns to `Strings::match()`/`matchAll()`/`split()`/`replace()` (and `replace()`'s
`pattern => replacement` map keys). Constant patterns only. Runs `Strings::match('', $pattern)`
and catches `RegexpException`, reporting only compilation errors (`getCode() === 0`) under
`nette.strings.regexpPattern`. A `Rule` gets **raw, non-normalized args**, so the pattern is
resolved by name (`pattern`) then positional index 1 via `StringsRegexHelper::findArg()` -
else a reordered `Strings::match(pattern: '#x#', subject: $s)` would validate `$s` as a regex.

### RethrowAbortExceptionRule

`Rule<TryCatch>`, namespace `Nette\PHPStan\Application`, **enabled by default**. Catches the
presenter bug where a broad `catch (\Throwable)`/`(\Exception)` swallows
`Nette\Application\AbortException` from `redirect()`/`forward()`/`terminate()`/`sendJson()`.
Fires only when both: (1) the try block can actually throw AbortException (recursive walk of
`MethodCall`/`StaticCall` `getThrowType()`), and (2) the first catch that is a supertype of
AbortException has no `throw` in its body. Identifier `nette.abortException`. Key decisions
(low false positives): throw-type match is one-directional (`$abortType->isSuperTypeOf($throwType)`
only, not the reverse); the AST walk does not descend into closures; conditional rethrow, nested
try/catch, and `finally` are accepted limitations.
