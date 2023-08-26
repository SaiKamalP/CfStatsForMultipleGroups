<?php

require_once __DIR__."/AlgorithmInterface.php";
class NoneAlgorithm implements AlgorithmInterface
{

    public function sign($key, $data)
    {
        return '';
    }

    public function verify($key, $data, $signature)
    {
        return (string) $signature === '';
    }
}
