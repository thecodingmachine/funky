<?php
declare (strict_types=1);

namespace TheCodingMachine\Funky;


class ContainerInjection implements Injection
{
    public function getCode(): string
    {
        return '$container';
    }
}
