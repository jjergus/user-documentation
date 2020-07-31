<?hh // strict

namespace Hack\UserDocumentation\AsyncOps\AsyncVsAwaitables\Examples\Impl;

<<__EntryPoint>>
function non_async_impl_main(): void {
  require_once "interface.inc.php";
}

class VolkswagenDiesel implements Car {
  public function drive(): Awaitable<void> {
    // ...
    return $this->driveNormally();
  }
  private async function driveNormally(): Awaitable<void> {
    // ...
  }
}
