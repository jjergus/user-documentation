```basic-usage.hack
/**
 * Query an arbitrary number of URLs in parallel
 * returning them as a Vector of string responses.
 */
async function get_urls(\ConstVector<string> $urls): Awaitable<Vector<string>> {

  // Await on curl requests in parallel and
  // prepend the request ID index
  return await \HH\Asio\vmk(
    $urls,
    async ($idx, $url) ==> {
      $content = await \HH\Asio\curl_exec($url);
      return $idx." => ".$content;
    },
  );
}

<<__EntryPoint>>
async function basic_usage_main(): Awaitable<void> {
  $urls = ImmVector {
    "http://example.com",
    "http://example.net",
    "http://example.org",
  };

  $pages = await get_urls($urls);
  foreach ($pages as $page) {
    echo \substr($page, 0, 20).' ... '.\substr($page, -8);
  }
}
```.skipif
// Skip if we don't have an internet connection
if (!\get_headers("www.example.com")) {
  print "skip";
}
```
