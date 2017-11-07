<?php
declare(strict_types=1);

namespace TheCodingMachine\Funky\Annotations;

use Doctrine\Common\Annotations\Annotation\Attribute;

/**
 * @Annotation
 * @Target({"METHOD"})
 * @Attributes({
 *   @Attribute("name", type = "string"),
 *   @Attribute("nameFromType", type = "bool"),
 *   @Attribute("nameFromMethodName", type = "bool"),
 *   @Attribute("aliases", type = "array<string>"),
 *   @Attribute("tags", type = "array<\TheCodingMachine\Funky\Annotations\Tag>")
 * })
 */
class Factory extends AbstractAnnotation
{
    /**
     * @var string[]
     */
    private $aliases = [];

    /**
     * @param mixed[] $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
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
}
