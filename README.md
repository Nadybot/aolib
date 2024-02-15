# Generic AMP3-based client and libraries for Anarchy Online Chat-bots in PHP

This is a very basic library that provides AMP3-based async classes to deal with connections to the chat-server of Anarchy Online. It consists of
* a tokenizer to extract single packets out of an endless stream (files, sockets, …)
* a connection-handler that utilizes the tokenizer to parse these string-packets into binary packets
* a parser that parses these binary packets into AO-packets
* an MMDB database class to access builtin-strings
* a basic client that combines all this, and keeps track of known uid <=> name mappings, as well as make the ever important lookup of these available as if these calls were sync

You have to be familiar with the AMP way of dealing with things, otherwise, this is useless to you.

## Basic usage

```php
<?php declare(strict_types=1);

use function Amp\Socket\connect;

use Monolog\Handler\StreamHandler;
use Monolog\Processor\PsrLogMessageProcessor;
use Monolog\{Level, Logger};

require_once __DIR__ . "/../vendor/autoload.php";

$logger = new Logger('mylogger');
$logger->pushHandler(new StreamHandler("php://stdout", Level::Debug));
$logger->pushProcessor(new PsrLogMessageProcessor(null, true));

$socket = connect("chat.d1.funcom.com:7105");
$client = new \AO\BasicClient(
    $logger,
    new \AO\Connection($logger, $socket, $socket);
    \AO\Package\Parser::createDefault($logger)
);
$client->login(username: "Myuser", password: "Mypassword", character: "Mychar");
while (($package = $client->read()) !== null) {
  // Process package
}
```

This just scratches the surface of what the library can do. I don't expect anyone else to use this code, so please step forward if I'm mistaken.

Check the code for usage of the client. Whether you extend it, or wrap it, is up to you, but extending is the more elegant solution.

### Lookups

Looking up names or UIDs is done like this

```php
$uid = $client->lookupUid("Nady");
$character = $client->lookupCharacter(123456);
```

All the logic happens behind the scenes. You either receive an int/string, or null.

### Sending packets

This is the most basic interface:

```php
$client->write(new Out\Tell(uid: 1234, message: "Hello!"));
```

## Running tests

Make sure you installed the developer packages as well and run `vendor/bin/phpunit -c phpunit.xml`

## Developing

The code has been written under VSCode using the following extensions:
* PHP Intelephense (bmewburn.vscode-intelephense-client)
* phpcs (shevaua.phpcs)
* phpstan (sanderronde.phpstan-vscode)
* PHPUnit Watcher (herisit.phpunit-watcher)
* Simple PHP CS Fixer 3 (phlak.simple-php-cs-fixer-3)

The shipped `.vscode/settings.json`-file should automatically configure them to work with this project.
