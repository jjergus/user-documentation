<?hh // partial
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the BSD-style license found in the
 *  LICENSE file in the root directory of this source tree. An additional grant
 *  of patent rights can be found in the PATENTS file in the same directory.
 *
 */
namespace HHVM\UserDocumentation;

use namespace HH\Lib\{C, Dict, Keyset, Regex, Str, Vec};

function inline_everything(string $path): bool {
  $incl = keyset[
    '/class.HH.Vector/shuffle',
    '/class.MCRouter/',
    '/class.MCRouterException/',
    '/class.MCRouterOptionException/',
  ];
  foreach ($incl as $i) {
    if (Str\contains($path, $i)) {
      return true;
    }
  }
  if (Str\contains_ci($path, 'mysql') && \file_exists($path.'.skipif')) {
    return true;
  }
  $expectf = $path.'.hhvm.expectf';
  if (
    \file_exists($expectf) && Str\contains(\file_get_contents($expectf), '%f')
  ) {
    return true;
  }
  return false;
}

<<__EntryPoint>>
function migrate_examples(): void {
  require_once(__DIR__.'/../vendor/autoload.hack');
  \Facebook\AutoloadMap\initialize();

  $nondeterministic = keyset[

  ];

  $hardcode_example_out = keyset[

  ];

  $dirs_with_files = keyset[];
  $rdi = new \RecursiveDirectoryIterator('api-examples-old');
  $rii = new \RecursiveIteratorIterator(
    $rdi,
    \RecursiveIteratorIterator::CHILD_FIRST,
  );
  foreach ($rii as $info) {
    if (!$info->isFile()) {
      continue;
    }
    $p = $info->getPathname();
    //echo "$p\n";
    $dir = \dirname($p);
    $dirs_with_files[] = $dir;
  }

  foreach ($dirs_with_files as $dir) {
    if (Str\contains_ci($dir, 'mysql')) {
      //continue; // TODO
    }

    $files = dict[];
    foreach (new \DirectoryIterator($dir) as $info) {
      if (!$info->isFile()) {
        continue;
      }
      list($name, $ext) = Str\split($info->getFilename(), '.', 2);
      $files[$name] ??= keyset[];
      $files[$name][] = $ext;
    }

    $output_parts = vec[];
    $files = Dict\sort_by_key($files);
    foreach ($files as $name => $exts) {
      $path = "$dir/$name";
      //echo "$dir/$name : ".Str\join($exts, ' ')."\n";
      if (
        !C\any(vec['php', 'md', 'hack'], $ext ==> C\contains_key($exts, $ext))
      ) {
        echo "INVALID $dir/$name : ".Str\join($exts, ' ')."\n";
        continue;
      }

      if (C\every(vec['php', 'hack'], $ext ==> C\contains_key($exts, $ext))) {
        echo "DUPLICATE $dir/$name : ".Str\join($exts, ' ')."\n";
        continue;
      }

      if (C\contains_key($exts, 'md')) {
        $md = Str\trim(\file_get_contents("$path.md"));
        if ($md !== '') {
          $output_parts[] = $md;
        }
      }

      if (C\contains_key($exts, 'php')) {
        $code_ext = 'php';
      } else if (C\contains_key($exts, 'hack')) {
        $code_ext = 'hack';
      } else {
        continue; // .md only
      }

      $trimmed = Str\trim_left($name, '0123456789-');
      $code = "```$trimmed.$code_ext";

      if (\file_exists("$path.$code_ext.no.auto.output")) {
        $code .= ' no-auto-output';
      }

      $code .= "\n";
      $code .= MarkdownExt\ExamplesIncludeBlock::getExampleBlock(
        "$path.$code_ext",
      ) as \Facebook\Markdown\UnparsedBlocks\FencedCodeBlock
        ->getContent() |> Str\trim($$)."\n";

      $inline_everything = inline_everything("$path.$code_ext");

      $extra_exts = keyset[];
      if ($inline_everything) {
        echo "FORCED: $path";
        $extra_exts[] = 'hhvm.expect';
        $extra_exts[] = 'hhvm.expectf';
        $extra_exts[] = 'example.hhvm.out';
      }
      $extra_exts[] = 'hhconfig';
      $extra_exts[] = 'ini';
      $extra_exts[] = 'skipif';

      foreach ($extra_exts as $ext) {
        //echo " ??? $path.$code_ext.$ext\n";
        if (\file_exists("$path.$code_ext.$ext")) {
          $code .= "```.$ext\n";
          $code .= \file_get_contents("$path.$code_ext.$ext")
            |> Str\trim($$)."\n";
        }
      }

      if ($inline_everything && Str\contains($path, 'MCRouter')) {
        $code .= "```.skipif\n";
        $code .= "\\Hack\\UserDocumentation\\API\\Examples\\MCRouter\\skipif();\n";
      }

      $code .= '```';
      $output_parts[] = $code;
    }
    $output = Str\join($output_parts, "\n\n")."\n";
    if (Str\trim($output) === '') {
      continue;
    }
    //echo "~~~ $dir.md\n$output\n";

    $out_path = Str\replace("$dir.md", 'api-examples-old', 'api-examples');
    $idx = 0;
    while (true) {
      $idx = Str\search($out_path, '/', $idx + 1);
      if ($idx is null) {
        break;
      }
      $new_dir = Str\slice($out_path, 0, $idx);
      if (!\file_exists($new_dir)) {
        //echo "mkdir $new_dir\n";
        \mkdir($new_dir);
      } else if (!\is_dir($new_dir)) {
        echo "NOT DIR: $new_dir\n";
      }
    }
    //echo "write $out_path\n";
    \file_put_contents($out_path, $output);
    //exit(0);
  }
  /*
    foreach ($ms as $m) {

      $ex = Str\trim($m[1]);
      echo "  $ex\n";
      if (Str\ends_with($ex, '.md')) {
        echo "    SKIP\n";
        continue;
      }
      invariant(
        Str\ends_with($ex, '.php') || Str\ends_with($ex, '.hack') ||
        Str\ends_with($ex, '.type-errors'),
        'Unknown type: %s',
        $ex,
      );
      if (!\file_exists("$dir/$ex")) {
        $ex = Str\slice(\basename($p), 0, 3).$ex;
      }
      echo "    $ex\n";
      $ex = "$dir/$ex";
      if (!\file_exists($ex)) {
        echo "      NOT FOUND\n";
        continue;
      }

      $code = MarkdownExt\ExamplesIncludeBlock::getExampleBlock($ex)
        as \Facebook\Markdown\UnparsedBlocks\FencedCodeBlock
        ->getContent();
      //echo "$code\n";

      $out = '```'.Str\replace(\basename($ex), '.php', '.php');
      if (\file_exists("$ex.no.auto.output")) { // TODO: and is empty
        $out .= ' no-auto-output';
      }
      $out .= "\n$code\n";

      // extra files
      $extra_exts = keyset[];

      if (
        \file_exists($ex.'.skipif') ||
        C\any($hardcode_example_out, $f ==> Str\ends_with($ex, $f))
      ) {
        $extra_exts[] = '.example.hhvm.out';
      }

      if (
        \file_exists($ex.'.skipif') ||
        C\any($nondeterministic, $nd ==> Str\ends_with($ex, $nd))
      ) {
        $ext = '.hhvm.expectf';
        if (!\file_exists($ex.$ext)) {
          $ext = '.expectf';
        }
        if (!\file_exists($ex.$ext)) {
          $ext = '.expectregex';
        }
        invariant(
          \file_exists($ex.$ext),
          'Missing .expectf for %s',
          $ex,
        );
        $extra_exts[] = $ext;
        //$out .= "```$ext\n";
        //$out .= \file_get_contents($ex.$ext) |> Str\trim($$)."\n";
      }

      $extra_exts[] = '.hhconfig';
      $extra_exts[] = '.ini';
      $extra_exts[] = '.skipif';

      foreach ($extra_exts as $ext) {
        if (\file_exists($ex.$ext)) {
          $out .= "```$ext\n";
          $out .= \file_get_contents($ex.$ext) |> Str\trim($$)."\n";
        }
      }

      $out .= '```';
      $c = Str\replace($c, $m[0], $out);
      //echo "~~~\n$c\n~~~\n";
    }
    \file_put_contents($p, $c);
  }*/
}
