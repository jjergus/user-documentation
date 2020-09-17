#!/bin/bash

set -ex

mv api-examples api-examples-old
mkdir api-examples
mv api-examples-old/__* api-examples/

hhvm bin/migrate.php

rm -rf api-examples-old

git add api-examples
git commit -qam "[auto] run migrate.php"

mkdir -p api-examples/_extracted/hack
mkdir -p api-examples/_extracted/hsl
mkdir -p api-examples/_extracted/hsl-experimental

hhvm bin/build.php

git add api-examples
git commit -qam "[auto] run build.php"

exit 0

git cherry-pick ae55c5d85c7c745d0e3491638fe2aeba0f51d7eb
git cherry-pick 995840e6e087c409eff17391e7e7a5349260dc1c

find guides -name "*.no.auto.output" | xargs git rm
git commit -am "[auto] delete .no.auto.outputs"

find guides -type d -name "*-examples" | xargs rm -rf

# restore
git checkout -- \
  guides/hack/03-expressions-and-operators/99-yield-examples/Testfile.txt \
  guides/hack/21-XHP/10-interfaces-examples/md.md_render.inc.php \
  guides/hack/15-asynchronous-operations/19-extensions-examples/async_mysql_require_env.inc.php \
  guides/hack/15-asynchronous-operations/19-extensions-examples/async_mysql_connect.inc.php \
  guides/hack/06-classes/16-constructors-examples/unpromotion.inc.hack

hhvm bin/build.php

git status --short | sed "s/^...//" | grep 'php$' | xargs git add
git status --short | sed "s/^...//" | grep 'hack$' | xargs git add
git status --short | sed "s/^...//" | grep 'type-errors$' | xargs git add
git commit -m "[auto] delete + run build.php (Hack files)"

git add .
git commit -am "[auto] delete + run build.php (expect + other files)"

vendor/bin/hacktest tests/ExamplesTest.php
