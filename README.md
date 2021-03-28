# librestful
librestful is a virion for [PocketMine](https://github.com/pmmp/PocketMine-MP) servers that make **easier**, **readable** code for **async** rest requests.

## Example
```php
use redmc\librestful\librestful;
use pocketmine\utils\InternetRequestResult;
use pocketmine\Server;

$client = librestful::create(
    Server::getInstance(),
    "http://api.redmc.me",
    [
        "Authorization" => "Bearer API_KEY"
    ]
);

$client->post()
    ->endpoint("player/ban")
    ->field("username", "eren5960")
    ->field("reason", "hack")
    ->result(fn(InternetRequestResult $result) => var_dump($result))
    ->fail(fn(string $error) => var_dump($error))
    ->async();
```

## Usage
### Requirements
- PHP 7.4+
- PocketMine 4.0.0 API
- [Devirion](https://github.com/poggit/devirion)

### Download
Download files and put to virions/ directory.

### GET Requests
```php
// import
use redmc\librestful\librestful;
use pocketmine\utils\InternetRequestResult;
use pocketmine\Server;

// create librestful client
$client = librestful::create(
    Server::getInstance(), // server 
    "http://api.redmc.me", // base url 
    ["Authorization" => "Bearer API_KEY"] // addinational headers for all requests
);

$playerName = "eren5960"; // example
$get = $client->get()
    ->endpoint("player/info/" . $playerName) //endpoint
   
    ->result(fn(InternetRequestResult $result) => var_dump($result)) // handle result
    ->fail(fn(string $error) => var_dump($error)) // handle error

    ->header("Cookie", "Key=value") // one header usage
    ->headers(["Connection" => "keep-alive"]) // multiple headers usage

    ->timeout(10); // timeout

$get->async(); // async run
$get->run(); // sync run (wait compilation)
```

### POST Requests
```php
// import
use redmc\librestful\librestful;
use pocketmine\utils\InternetRequestResult;
use pocketmine\Server;

// create librestful client
$client = librestful::create(
    Server::getInstance(), // server 
    "http://api.redmc.me", // base url 
    ["Authorization" => "Bearer API_KEY"] // addinational headers for all requests
);

// post
$post = $client->post()
    ->endpoint("player/ban/") //endpoint

    ->field("username", "eren5960") // one field usage
    ->fields([
        "reason" => "hack",
        "time" => strtotime("+1 months") 
    ]) // multiple field usage

    ->header("Cookie", "Key=value") // one header usage
    ->headers(["Connection" => "keep-alive"]) // multiple headers usage

    ->result(fn(InternetRequestResult $result) => var_dump($result)) // handle result
    ->fail(fn(string $error) => var_dump($error)) // handle error

    ->timeout(10); // timeout
$post->async(); // async run
$post->run(); // sync run (wait compilation)
```