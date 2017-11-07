<?php
declare(strict_types=1);

namespace TheCodingMachine\Funky\Annotations;

/**
 * @Annotation
 * @Target({"METHOD"})
 * @Attributes({
 *   @Attribute("name", type = "string"),
 *   @Attribute("nameFromType", type = "bool"),
 *   @Attribute("nameFromMethodName", type = "bool"),
 *   @Attribute("tags", type = "array<\TheCodingMachine\Funky\Annotations\Tag>")
 * })
 */
class Extension extends AbstractAnnotation
{

}
