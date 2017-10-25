<?php
declare (strict_types=1);

namespace TheCodingMachine\Funky\Injections;


class ServiceInjection implements Injection
{
    /**
     * @var string
     */
    private $serviceName;
    /**
     * @var bool
     */
    private $compulsory;

    public function __construct(string $serviceName, bool $compulsory)
    {
        $this->serviceName = $serviceName;
        $this->compulsory = $compulsory;
    }

    public function getCode(): string
    {
        $code = '$container->get('.var_export($this->serviceName, true).')';

        if (!$this->compulsory) {
            $code = sprintf('$container->has(%s)?%s:null', var_export($this->serviceName, true), $code);
        }

        return $code;
    }
}
