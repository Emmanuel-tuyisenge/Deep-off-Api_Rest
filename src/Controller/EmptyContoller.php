<?php

namespace App\Controller;

class EmptyContoller
{
    public function __invoke($data)
    {
        return $data;
    }
}
