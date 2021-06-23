<?php

namespace redmc\librestful;

class Result {
    private $ok;
    private ?\Exception $error;

    public function __construct($ok, ?\Exception $error = null){
        $this->ok = $ok;
        $this->error = $error;
    }

    public function match(?callable $ok, ?callable $error = null): void{
        if ($this->error !== null) {
            if ($error !== null) {
                $error($this->error);
            }
            return;
        }

        if ($this->ok !== null) {
            $ok($this->ok);
            return;
        }

        throw new \InvalidStateException("Tried match to null result.");
    }
}