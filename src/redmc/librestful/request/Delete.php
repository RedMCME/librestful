<?php

declare(strict_types=1);

namespace redmc\librestful\request;

use pocketmine\utils\Internet;
use pocketmine\utils\InternetException;
use pocketmine\utils\InternetRequestResult;
use redmc\librestful\exceptions\RequestErrorException;
use redmc\librestful\Method;
use redmc\librestful\Utils;

abstract class Delete extends Post{
    public function getMethod(): Method{
        return Method::DELETE();
    }

    public function executeFn(): callable{
        return static function (
            string $url,
            array $fields,
            int $timeout,
            array $headers
        ): ?InternetRequestResult{
            $error = null;
            $result = null;
            try{
                $result = Internet::simpleCurl(
                    $url,
                    $timeout,
                    Utils::fixedHeaders($headers),
                    [
                        CURLOPT_POSTFIELDS => $fields,
                        CURLOPT_CUSTOMREQUEST => "DELETE",
                    ]
                );
            }catch(InternetException $ex){
                $error = $ex->getMessage();
            }

            if($error !== null){
                throw new RequestErrorException($error, $this);
            }
            return $result;
        };
    }
}
