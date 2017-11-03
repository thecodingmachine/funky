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
 * })
 */
class Extension
{
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
            throw new AnnotationException('Extension should have only one property in the list "name", "nameFromType", '
                .'"nameFromMethodName".');
        }
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
