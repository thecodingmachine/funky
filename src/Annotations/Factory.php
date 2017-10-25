<?php
declare(strict_types=1);

namespace TheCodingMachine\Funky\Annotations;
use Doctrine\Common\Annotations\AnnotationException;

/**
 * @Annotation
 * @Target({"METHOD"})
 * @Attributes({
 *   @Attribute("name", type = "string"),
 *   @Attribute("nameFromType", type = "bool"),
 *   @Attribute("nameFromMethodName", type = "bool"),
 *   @Attribute("aliases", type = "array<string>"),
 * })
 */
class Factory
{
    /**
     * @var string[]
     */
    private $aliases = [];
    /**
     * @var bool
     */
    private $nameFromType = false;
    /**
     * @var bool
     */
    private $nameFromMethodName = false;
    /**
     * @var string|null
     */
    private $name;

    /**
     * @param mixed[] $attributes
     */
    public function __construct(array $attributes = [])
    {
        $count = 0;
        if (isset($attributes['nameFromType'])) {
            $this->nameFromType = $attributes['nameFromType'];
            if ($this->nameFromType) {
                $count++;
            }
        }
        if (isset($attributes['nameFromMethodName'])) {
            $this->nameFromMethodName = $attributes['nameFromMethodName'];
            if ($this->nameFromMethodName) {
                $count++;
            }
        }
        if (isset($attributes['name'])) {
            $this->name = $attributes['name'];
            $count++;
        }

        if ($count === 0) {
            $this->nameFromType = true;
        }
        if ($count > 1) {
            throw new AnnotationException('Factory should have only one property in the list "name", "nameFromType", "nameFromMethodName". You can add aliases if you need several names.');
        }

        if (isset($attributes['aliases']) && \is_array($attributes['aliases'])) {
            $this->setAliases(...$attributes['aliases']);
        }
    }

    /**
     * Helper method to ensure that the passed aliases are of string type.
     *
     * @param string[] $aliases
     */
    private function setAliases(string ...$aliases): void
    {
        $this->aliases = $aliases;
    }
    /**
     * Returns the list of aliases for the bean instance. Returns an empty array when no alias was set.
     *
     * @return string[]
     */
    public function getAliases(): array
    {
        return $this->aliases;
    }
    public function getName(): ?string
    {
        return $this->name;
    }
    public function isFromType(): bool
    {
        return $this->nameFromType;
    }
    public function isFromMethodName(): bool
    {
        return $this->nameFromMethodName;
    }
}
