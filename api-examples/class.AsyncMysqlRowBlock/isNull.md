The following example uses `AsyncMysqlRowBlock::isNull` to check if a field value is `null` (e.g., if a field was set in SQL to something like `age SMALLINT NULL`, then that field *could* be `null`).

```basic-usage.hack
use \Hack\UserDocumentation\API\Examples\AsyncMysql\ConnectionInfo as CI;

async function connect(
  \AsyncMysqlConnectionPool $pool,
): Awaitable<\AsyncMysqlConnection> {
  return await $pool->connect(
    CI::$host,
    CI::$port,
    CI::$db,
    CI::$user,
    CI::$passwd,
  );
}
async function simple_query(): Awaitable<int> {
  $pool = new \AsyncMysqlConnectionPool(darray[]);
  $conn = await connect($pool);
  $result = await $conn->query('SELECT * FROM test_table WHERE userID < 50');
  $conn->close();
  // A call to $result->rowBlocks() actually pops the first element of the
  // row block Vector. So the call actually mutates the Vector.
  $row_blocks = $result->rowBlocks();
  if (!$row_blocks->isEmpty()) {
    // An AsyncMysqlRowBlock
    $row_block = $row_blocks[0];
    if (!$row_block->isNull(0, 'age')) {
      return $row_block->getFieldAsInt(0, 'age');
    }
    return -1;
  } else {
    return -1;
  }
}

<<__EntryPoint>>
async function run(): Awaitable<void> {
  $r = await simple_query();
  \var_dump($r);
}
```.hhvm.expectf
int(%d)
```.example.hhvm.out
int(42)
```.skipif
await \Hack\UserDocumentation\API\Examples\AsyncMysql\skipif_async();
```
