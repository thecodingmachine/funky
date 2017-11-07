<?php
declare(strict_types=1);

namespace TheCodingMachine\Funky\Annotations;

/**
 * @Annotation
 * @Target({"ANNOTATION"})
 * @Attributes({
 *   @Attribute("name", type = "string", required=true),
 *   @Attribute("priority", type = "float"),
 * })
 */
class Tag
{
    /**
     * @var string
     */
    private $name;
    /**
     * @var float
     */
    private $priority;

    /**
     * @param mixed[] $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->name = $attributes['name'];
        $this->priority = $attributes['priority'] ?? 0.0;
    }

    public function getName(): string
    {
        return $this->name;
    }
    public function getPriority(): float
    {
        return $this->priority;
    }
}
