This page describes experimental features that could eventually make their way
into the Hack language.


## Reactive Hack

**Current status:** Working prototype.

The goal of Reactive Hack is to enable subscribing to the results of a function
and efficiently get updates as they occur. The ability to subscribe to arbitrary
logic would enable cross-request caching with smart invalidation, realtime data
synchronization between client and server, and other cool scenarios.

Taking advantage of Reactive Hack is only possible if the full stack, including
any backend services (like databases) supports it. The current implementation of
Reactive Hack is very experimental and we wouldn't recommend anyone to try
building compatible backends just yet.

The `<<__Rx>>` attribute, which you might see on various Hack Standard Library
or built-in functions, marks a specific function or method as "reactive". The
type checker than enforces various restrictions on this function, most
importantly that it can't have any side effects or call any non-`<<__Rx>>`
functions.

You may also see other similar attributes (e.g. `<<__RxShallow>>`) which only
exist to make it easier to migrate from non-reactive code, and are not meant to
be part of the language in the long term.

This project has in part evolved from the [Skip language](http://skiplang.com/),
which is no longer in active development, but has a fully working prototype that
you can try out, and all its code is available as open-source.

Note: It is sometimes useful to use the `<<__Rx>>` attribute to enforce that a
function is "pure" (has no side effects), even if not running Reactive Hack.
But again, since the feature is experimental, don't rely on it unless you're
ready to migrate your code if anything changes.


## XHP namespaces

**Current status:** Design phase.

This feature is designed and implemented by an external contributor, for which
we are very thankful!

Since [XHP](https://docs.hhvm.com/hack/XHP) was created before Hack supported
namespaces, it was not designed with namespaces in mind, and namespace support
for XHP has never been implemented.

A particularly tricky part here is dealing with XHP element names containing the
`:` symbol, which was often used to simulate namespaces. One possibility is to
simply use `:` instead of `\` for XHP namespaces, but the design has not been
finalized yet.


## Records

**Current status:** Design phase.

Limitations of [shapes](https://docs.hhvm.com/hack/built-in-types/shapes) are a
common complaint, so we're working on a more powerful replacement.

The design has not been finalized yet, but it could look something like this:

```Hack
abstract record Foo {
  x: int = 10,
  y: string,
}

final record Bar extends Bar {
  z: double,
}
```


## Pocket Universes

**Current status:** Early working prototype (expect major changes).

Over time, we've noticed Hack developers increasingly relying on code generation
to bypass limitations of our type system. For example, one might create a script
that takes a database schema and generates a fully typed Hack class to represent
each entity loaded from this database.

Code generation makes sense when it embodies a non-trivial domain-specific
compilation scheme. For example, a parser generator compiles a grammar into an
optimized, table-driven implementation that is quite different from 'normal'
compilation. But most of our code generation isn't like that: we're just spewing
out large amounts of boring boilerplate to impedance-match between Hack and
external types, such as entities loaded from a database. The need to do that
indicates a lack of expressiveness in our language.

The goal of Pocket Universes is to beef up Hack's type system with just enough
power to be able to replace uses of meta-programming (code generation) by
generic programming (more polymorphism).

The design may still change, but here's an example using the current prototype
implementation:

```Hack
abstract class DBEntity {
  // Untyped information loaded from a database
  private dict<string, string> $datastore = dict[];

  // Specification of Pocket Universe's fields
  enum Field {
    // Each member of the enumeration should specify the "cases" information
    // below:
    case type T; // Hack type associated with the field
    case string ident; // name associated with field
    case T default_value; // a default value
    case Serializer<T> serializer; // way to persist values
  }

  // The generic functions to manipulate an Entity. Note new syntax: $f is a
  // value that will be a Field of some subclass of Entity, and the return value
  // will be of the type T associated with that value.
  public final function get<TF from this::Field>(TF $f): TF::T {
    $ident = static::Field::ident($f);
    $value = idx($this->datastore, $ident);
    if ($value is null) {
      return static::Field::default_value($f);
    }
    $deser = static::Field::serializer($f);
    return $deser->deserialize($value);
  }

  // ... similar set method elided for brevity
}

// A concrete entity extends the class DBEntity and defines the fields.
final class Pet extends DBObject {
  enum Field {
    :@name(
      type T = string,
      ident = "name",
      default_value = "Noname",
      serializer = new StringSerializer()
    );
    :@species(
      type T = Animal,
      ident = "species",
      default_value = new Cthulhu(),
      serializer = new AnimalSerializer()
    );
    :@birthday(
      type T = int,
      ident = "birthday",
      default_value = time(),
      serializer = new DateSerializer()
    );
  }
}

// Sample code that uses an entity
function greet(Pet $pet): void {
  $name = $pet->get(:@name);
  if ($name === Pet::default_value(:@name)) {
    echo "Your ".$pet->get(:@species)." is sad to not have a name\n";
  } else {
    echo $name." greets you !\n";
  }
}
