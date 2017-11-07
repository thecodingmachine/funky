<?php
declare(strict_types=1);

namespace TheCodingMachine\Funky\Annotations;

use Doctrine\Common\Annotations\AnnotationException;

abstract class AbstractAnnotation
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
     * @var Tag[]
     */
    private $tags = [];

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
            throw new AnnotationException('There should be only one property in the list "name", "nameFromType", '
                .'"nameFromMethodName". You can add aliases if you need several names.');
        }

        if (isset($attributes['tags']) && \is_array($attributes['tags'])) {
            $this->setTags(...$attributes['tags']);
        }
    }

    /**
     * Helper method to ensure that the passed tags are of Tag type.
     *
     * @param Tag[] $tags
     */
    private function setTags(Tag ...$tags): void
    {
        $this->tags = $tags;
    }
    /**
     * Returns the list of tags for the bean instance. Returns an empty array when no tag was set.
     *
     * @return Tag[]
     */
    public function getTags(): array
    {
        return $this->tags;
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
