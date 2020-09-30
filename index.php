<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);


include 'vendor/autoload.php';

use Ackintosh\Ganesha\Builder;
use GuzzleHttp\Client;

$memcached = new \Memcached();
$memcached->addServer("memcached", 11211);
$adapter = new Ackintosh\Ganesha\Storage\Adapter\Memcached($memcached);

$ganesha = Ackintosh\Ganesha\Builder::withCountStrategy()
    // The failure count threshold that changes CircuitBreaker's state to `OPEN`.
    // The count will be increased if `$ganesha->failure()` is called,
    // or will be decreased if `$ganesha->success()` is called.
    ->failureCountThreshold(1)
    // The interval (seconds) to change CircuitBreaker's state from `OPEN` to `HALF_OPEN`.
    ->intervalToHalfOpen(5)
    // The storage adapter instance to store various statistics to detect failures.
    ->adapter(new Ackintosh\Ganesha\Storage\Adapter\Memcached($memcached))
    ->build();

$middleware = new \Ackintosh\Ganesha\GuzzleMiddleware($ganesha);
$mock =  new \GuzzleHttp\Handler\MockHandler([
    new \GuzzleHttp\Psr7\Response(200),
    new \GuzzleHttp\Psr7\Response(200),
    new \GuzzleHttp\Psr7\Response(200),
    new \GuzzleHttp\Psr7\Response(200),
//    new \GuzzleHttp\Exception\BadResponseException('teste', new \GuzzleHttp\Psr7\Request('GET', 'teste')),
//    new \GuzzleHttp\Exception\BadResponseException('teste', new \GuzzleHttp\Psr7\Request('GET', 'teste')),
    new \GuzzleHttp\Exception\BadResponseException('teste', new \GuzzleHttp\Psr7\Request('GET', 'teste')),
//    new \GuzzleHttp\Psr7\Response(400),
    new \GuzzleHttp\Psr7\Response(400),
    new \GuzzleHttp\Psr7\Response(400),
    new \GuzzleHttp\Psr7\Response(400),
    new \GuzzleHttp\Psr7\Response(200),
    new \GuzzleHttp\Psr7\Response(200),
    new \GuzzleHttp\Psr7\Response(200),
]);

$handlers = \GuzzleHttp\HandlerStack::create($mock);
//$handlers = \GuzzleHttp\HandlerStack::create();
$handlers->push($middleware);
$client = new Client([
    'base_uri' => 'http://demo4446522.mockable.io/',
    'handler' => $handlers,
]);

for ($i = 0; $i < 10; $i++) {

    echo "TENTATIVA {$i} ".date('i:s')."\n";

    try {
//        sleep(10);
        $client->get('foo');
        echo "Success Request\n";
    } catch (\GuzzleHttp\Exception\RequestException $guzzleException) {
        echo "Guzzle Exception: ".$guzzleException->getMessage()."\n";
    } catch (\Ackintosh\Ganesha\Exception\RejectedException $rejectedException) {
        echo "Circuit Exception: ".$rejectedException->getMessage()."\n";
        break;
    }
}

