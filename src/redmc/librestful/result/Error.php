<?php

namespace redmc\librestful;

class Error extends Result {
    public function __construct($error){
        parent::__construct(null, $error);
    }

    public static function from($error): Error{
        return new Error($error);
    }

    public static function fromString(string $error): Error{
        return new Error(new \Exception($error));
    }
}