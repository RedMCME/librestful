<?php

namespace redmc\librestful;

class Result {
    protected $ok;
    protected $error;

    public function __construct($ok, $error = null){
        $this->ok = $ok;
        $this->error = $error;
    }

    public function match(?callable $ok, ?callable $error = null): void{
        if ($this->error !== null) {
            if ($error !== null) {
                if ($this->error instanceof AsParams) {
                    $ok(...$this->error->getValue());
                    return;
                }

                $error($this->error);
            }
            return;
        }

        if ($this->ok !== null) {
            if ($this->ok instanceof AsParams) {
                $ok(...$this->ok->getValue());
                return;
            }

            $ok($this->ok);
            return;
        }

        throw new \InvalidStateException("Tried match to null result.");
    }

    public function okValue() {
        return $this->ok;
    }

    public function errorValue() {
        return $this->error;
    }

    public function valid(): bool{
        return $this->error === null;
    }

    public function invalid(): bool{
        return ! $this->valid();
    }
}