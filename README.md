# librestful
librestful is a virion for [PocketMine](https://github.com/pmmp/PocketMine-MP) servers that make **easier**, **readable** and **async** code for rest requests.

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
$client->post(
    "player/ban",
    [
        "username" => "eren5960",
        "reason" => "hack"
    ],
    function(InternetRequestResult $result) {
        var_dump($result);
    },
    function(string $error) {
        var_dump($error);
    },
);
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
$client->get(
    "player/info/" . $playerName, // endpoint
    function(InternetRequestResult $result) { // on response
        // handle result
        var_dump($result);
    },
    function(string $error) { // on fail
        // handle error
        var_dump($error);
    },
    10, // timeout, default 10
    [] // custom headers, like: ["Connection" => "keep-alive"]
);
```

### POST requests
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
$client->post(
    "player/ban", // endpoint
    [
        "username" => "eren5960",
        "reason" => "hack"
    ], // post args (fields, data)
    function(InternetRequestResult $result) { // on response
        // handle result
        var_dump($result);
    },
    function(string $error) { // on fail
        // handle error
        var_dump($error);
    },
    10, // timeout, default 10
    [] // custom headers, like: ["Connection" => "keep-alive"]
);
```