# librestful
librestful is a virion for [PocketMine](https://github.com/pmmp/PocketMine-MP) servers that make **easier**, **readable** code for **async** rest requests.

## Example
```php
use redmc\librestful\librestful;
use redmc\librestful\Response;

$client = librestful::create(
    "http://api.redmc.me",
    [
        "Authorization" => "Bearer API_KEY"
    ]
);

$client->post()
    ->endpoint("player/ban")
    ->field("username", "eren5960")
    ->field("reason", "hack")
    ->result(fn(Response $result) => var_dump($result))
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
use redmc\librestful\Response;

// create librestful client
$client = librestful::create(
    "http://api.redmc.me", // base url 
    ["Authorization" => "Bearer API_KEY"] // addinational headers for all requests
);

$playerName = "eren5960"; // example
$get = $client->get()
    ->endpoint("player/can_access/" . $playerName) //endpoint

    ->param("where", "win") // one parameter usage
    ->params([
        "format" => "json",
        "period" => "monthly"
    ]) // multiple parameter usage

    ->player($playerName) // use player instance on result
    ->result(function(Response $result) use($playerName){
        $player = $result->player($playerName);
        if ($player === null) return;
        
        if ($result->code() === 401) { // unauhorized, this an example
            $player->kick("unauthorized");
        }
    }) // handle result
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
use redmc\librestful\Response;

// create librestful client
$client = librestful::create(
    "http://api.redmc.me", // base url 
    ["Authorization" => "Bearer API_KEY"] // addinational headers for all requests
);

// post
$post = $client->post()
    ->endpoint("player/ban") //endpoint

    ->field("username", "eren5960") // one field usage
    ->fields([
        "reason" => "hack",
        "time" => strtotime("+1 months") 
    ]) // multiple field usage

    ->header("Cookie", "Key=value") // one header usage
    ->headers(["Connection" => "keep-alive"]) // multiple headers usage

    ->result(fn(Response $result) => var_dump($result)) // handle result
    ->fail(fn(string $error) => var_dump($error)) // handle error

    ->timeout(10); // timeout

$post->async(); // async run
$post->run(); // sync run (wait compilation)
```

## More Examples

### Minecraft Pocket Server Get List of Voters
```php
use redmc\librestful\librestful;
use redmc\librestful\Response;

$client = librestful::create("https://minecraftpocket-servers.com/api");

$client
    ->get()

    ->param("object", "servers")
    ->param("element", "voters")
    ->param("key", "Your API Key")
    ->param("month", "current")
    ->param("format", "json")

    ->result(fn(Response $response) => var_dump(json_decode($response->body(), true)))
    ->fail(fn(string $error) => var_dump("unable to get monthly voters: " . $error))

    ->timeout(10)
    ->async();
```