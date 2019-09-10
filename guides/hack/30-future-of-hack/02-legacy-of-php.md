In December 2018, HHVM
[stopped supporting PHP](https://hhvm.com/blog/2018/09/12/end-of-php-support-future-of-hack.html).
Since then, we've been working on migrating away from various PHP features that
are no longer needed or don't fit well with the Hack language -- most of these
were only part of the language to maintain compatibility with PHP.


## Arrays

**Current status:** Replacement ready, removal expected eventually.

See [Built In Types: Arrays](https://docs.hhvm.com/hack/built-in-types/arrays).

As described there, replacements for the legacy `array` type (`vec`, `dict` and
`keyset`) are fully ready and we recommend everyone to use them.

We don't have a timeline for removing the legacy `array` type from the language
completely yet, but it is something we are hoping to eventually do.


## Passing function arguments by reference

**Current status:** Replacement ready, removal expected soon.

See [References (deprecated)](https://docs.hhvm.com/hack/functions/inout-parameters#references-deprecated).

As described there, references (`int &$param`) have been deprecated in favor of
*inout parameters* (`inout int $param`), which have clearer semantics and are
fully understood by the type checker.

We recommend using inout parameters instead of references everywhere in your
code. References will likely be completely removed from the language soon.

Until that happens, to make the migration easier, it is possible to use
references and inout parameters interchangeably (see
[Migrating to inout parameters](https://docs.hhvm.com/hack/functions/inout-parameters#references-deprecated__migrating-to-inout-parameters)).
Therefore, you can migrate your function declarations and function calls
separately.

We are currently working on migrating all built-in functions that take one or
more arguments by reference to use inout parameters instead. In most cases,
this should be largely invisible to Hack developers (because of the
aforementioned interchangeability), but in some cases we need to break
compatibility with the old API -- most commonly to avoid optional inout
parameters, which are not allowed. For example, see the list of functions and
their replacements added in
[HHVM 4.21](https://hhvm.com/blog/2019/09/03/the-hhvm-4.21.0.html).

You can use `hhast-migrate --ref-to-inout` (added in HHAST 4.21.7) to
automatically migrate all your calls to built-in functions.


## `PHP\` namespace

**Current status:** Implementation in progress.

We are working on moving most built-in functions that were inherited from PHP
into a separate namespace -- for example, `\array_map()` becomes
`\PHP\array_map()`.

When this happens, we will have an HHAST migration ready and we expect both
variants of each function to be available during the transition.


## Class constants

**Current status:** Replacement should be ready soon.

We are working on replacing class constants:

```Hack
class LifeUniverseAndEverything {
  const int ANSWER = 42;
}
```

with a new `<<__Const>>` modifier on class variables (properties):

```Hack
class LifeUniverseAndEverything {
  <<__Const>>
  public int $answer = 42;
}
```

This simplifies the Hack language as well as the runtime (we no longer need to
keep separate tables of constants and variables for a class).


## No big syntactic changes

We occasionally receive suggestions for major changes to the Hack syntax (common
examples are removal of `$` to denote variables, or changing the order we
declare types for function parameters to `$param: type`).

We are **not** planning to make any such changes in the near future.

It is possible that we will implement changes of this type in the far future,
but for now, we are concentrating on all the other projects listed here, which
we think are more impactful. Our main priority right now is to get Hack closer
to a statically typed language.
