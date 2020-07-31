<?hh // strict

namespace Hack\UserDocumentation\AsyncOps\AsyncVsAwaitables\Examples\Impl;

<<__EntryPoint>>
function async_impl_main(): void {
  require_once "interface.inc.php";
}

class Ford implements Car {
  public async function drive(): Awaitable<void> {
    // ...
  }
}
