<?hh // strict
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the BSD-style license found in the
 *  LICENSE file in the root directory of this source tree. An additional grant
 *  of patent rights can be found in the PATENTS file in the same directory.
 *
 */

namespace Facebook\Markdown\UnparsedBlocks;

use type Facebook\Markdown\Blocks\BlockQuote as ASTNode;
use namespace Facebook\Markdown\Inlines;
use namespace HH\Lib\{C, Str, Vec};

final class BlockQuote extends ContainerBlock {
  public function __construct(
    private vec<Block> $children,
  ) {
  }

  public static function consume(
    Context $context,
    Lines $lines,
  ): ?(Block, Lines) {
    list($matched, $rest) = $lines->getPrefixedLinesAndRest(
      $context,
      '/^ {0,3}> ?/',
    );
    if ($matched->isEmpty()) {
      return null;
    }
    return tuple(new self(self::consumeChildren($context, $matched)), $rest);
  }

  public function withParsedInlines(Inlines\Context $ctx): ASTNode {
    return new ASTNode(
      Vec\map(
        $this->children,
        $child ==> $child->withParsedInlines($ctx),
      ),
    );
  }
}
