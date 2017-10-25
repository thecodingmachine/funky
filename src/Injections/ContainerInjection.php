<?php
declare (strict_types=1);

namespace TheCodingMachine\Funky\Injections;


class ContainerInjection implements Injection
{
    public function getCode(): string
    {
        return '$container';
    }
}
