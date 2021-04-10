# librestful
librestful is a virion for [PocketMine](https://github.com/pmmp/PocketMine-MP) servers that make **easier**, **readable** code for **async** rest requests.

## Example
```php
use redmc\librestful\exceptions\RequestErrorException;
use redmc\librestful\librestful;
use redmc\librestful\Response;

$client = librestful::create(
    $plugin,
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
    ->fail(fn(RequestErrorException $error) => var_dump($error))

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
use redmc\librestful\exceptions\RequestErrorException;
use redmc\librestful\librestful;
use redmc\librestful\Response;

// create librestful client
$client = librestful::create(
    $plugin, // plugin instance (\pocketmine\plugin\Plugin)
    "http://api.redmc.me", // base url 
    ["Authorization" => "Bearer API_KEY"], // addinational headers for all requests
    2, // thread count
    true // logging requests
);

$player = Player; // a player
$get = $client->get()
    ->endpoint("player/can_access/" . $playerName) //endpoint

    ->param("where", "win") // one parameter usage
    ->params([
        "format" => "json",
        "period" => "monthly"
    ]) // multiple parameter usage

    ->result(function(Response $result) use($player){
        if (!$player->isOnline()) return;
        
        if ($result->code() === 401) { // unauthorized, this an example
            $player->kick("unauthorized");
        }
    }) // handle result
    ->fail(fn(RequestErrorException $error) => var_dump($error)) // handle error
    ->finally(fn() => var_dump("It's over.")) // on finish

    ->header("Cookie", "Key=value") // one header usage
    ->headers(["Connection" => "keep-alive"]) // multiple headers usage

    ->timeout(5); // timeout, default 10

$get->async(); // async run
$client->waitAll(); // wait to finish requests
//or
$get->run(); // sync run (wait compilation)
```

### POST Requests
```php
// import
use redmc\librestful\exceptions\RequestErrorException;
use redmc\librestful\librestful;
use redmc\librestful\Response;

// create librestful client
$client = librestful::create(
    $plugin, // plugin instance (\pocketmine\plugin\Plugin)
    "http://api.redmc.me", // base url 
    ["Authorization" => "Bearer API_KEY"], // addinational headers for all requests
    2, // thread count
    true // logging requests
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
    ->fail(fn(RequestErrorException $error) => var_dump($error)) // handle error
    ->finally(fn() => var_dump("It's over.")) // on finish

    ->timeout(5); // timeout, default 10

$post->async(); // async run
$client->waitAll(); // wait to finish requests
//or
$post->run(); // sync run (wait compilation)
```

## More Examples

### Minecraft Pocket Server Get List of Voters
```php
use redmc\librestful\exceptions\RequestErrorException;
use redmc\librestful\librestful;
use redmc\librestful\Response;

$client = librestful::create($plugin, "https://minecraftpocket-servers.com/api");

$client
    ->get()

    ->param("object", "servers")
    ->param("element", "voters")
    ->param("key", "Your API Key")
    ->param("month", "current")
    ->param("format", "json")

    ->result(fn(Response $response) => var_dump(json_decode($response->body(), true)))
    ->fail(fn(RequestErrorException $error) => var_dump("unable to get monthly voters: " . $error->getMessage()))

    ->timeout(15)
    ->async();
```