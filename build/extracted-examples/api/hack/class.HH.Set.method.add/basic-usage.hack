// WARNING: Contains some auto-generated boilerplate code, see:
// HHVM\UserDocumentation\MarkdownExt\ExtractedCodeBlocks\FilterBase::addBoilerplate

namespace HHVM\UserDocumentation\Api\Hack\ClassHHSetMethodAdd\BasicUsage;

<<__EntryPoint>>
async function _main(): Awaitable<void> {
  \init_docs_autoloader();

  $s = Set {};

  $s->add('red');
  \var_dump($s);

  // Set::add returns the Set so it can be chained
  $s->add('green')
    ->add('blue')
    ->add('yellow');
  \var_dump($s);

  // Adding an element that already exists in the Set has no effect
  $s->add('green')
    ->add('blue')
    ->add('yellow');
  \var_dump($s);
}
