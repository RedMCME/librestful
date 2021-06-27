<?php

namespace redmc\librestful;

class Ok extends Result {
    public function __construct($ok){
        parent::__construct($ok, null);
    }

    public static function from($ok): Ok{
        return new Ok($ok);
    }
}