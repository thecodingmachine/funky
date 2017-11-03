<?php
declare(strict_types=1);

namespace TheCodingMachine\Funky;

use Doctrine\Common\Annotations\AnnotationReader;
use Interop\Container\ServiceProviderInterface;
use ReflectionClass;
use TheCodingMachine\Funky\Annotations\Factory;
use TheCodingMachine\Funky\Utils\FileSystem;

class ServiceProvider implements ServiceProviderInterface
{
    /**
     * @var ReflectionClass
     */
    private $refClass;

    private static $annotationReader;


    private static function getAnnotationReader() : AnnotationReader
    {
        if (self::$annotationReader === null) {
            self::$annotationReader = new AnnotationReader();
        }
        return self::$annotationReader;
    }

    /**
     * @return FactoryDefinition[]
     * @throws \TheCodingMachine\Funky\BadModifierException
     */
    private function getFactoryDefinitions(): array
    {
        $refClass = new ReflectionClass($this);
        $factories = [];

        foreach ($refClass->getMethods() as $method) {
            $factoryAnnotation = self::getAnnotationReader()->getMethodAnnotation($method, Factory::class);
            if ($factoryAnnotation) {
                $factories[] = new FactoryDefinition($method, $factoryAnnotation);
            }
        }

        return $factories;
    }

    /**
     * Returns a list of all container entries registered by this service provider.
     *
     * - the key is the entry name
     * - the value is a callable that will return the entry, aka the **factory**
     *
     * Factories have the following signature:
     *        function(\Psr\Container\ContainerInterface $container)
     *
     * @return callable[]
     */
    public function getFactories()
    {
        [$className, $fileName] = $this->getFileAndClassName();
        if (!file_exists($fileName) || filemtime($this->getRefClass()->getFileName()) > filemtime($fileName)) {
            $this->dumpHelper();
        }

        require_once $fileName;

        $factoriesCallable = $className.'::getFactories';

        return $factoriesCallable();
    }

    /**
     * Writes the helper class and returns the file path.
     *
     * @return string
     * @throws \TheCodingMachine\Funky\IoException
     */
    public function dumpHelper(): string
    {
        [$className, $tmpFile] = $this->getFileAndClassName();

        FileSystem::mkdir(dirname($tmpFile));
        $result = file_put_contents($tmpFile, $this->dumpServiceProviderHelper($className));
        if ($result === false) {
            throw IoException::cannotWriteFile($tmpFile);
        }
        return $tmpFile;
    }

    /**
     * @return string[] Returns an array with 2 items: the class name and the file name.
     */
    private function getFileAndClassName(): array
    {
        $refClass = $this->getRefClass();
        $className = $this->getClassName();

        $fileName = sys_get_temp_dir().'/funky_cache/'.str_replace(':', '', dirname($refClass->getFileName()).'/'.str_replace('\\', '__', $className).'.php');

        return [$className, $fileName];
    }

    private function getRefClass(): ReflectionClass
    {
        if ($this->refClass === null) {
            $this->refClass = new ReflectionClass($this);
        }
        return $this->refClass;
    }

    private function getClassName(): string
    {
        $className = get_class($this).'Helper';
        if ($this->getRefClass()->isAnonymous()) {
            $className = preg_replace("/[^A-Za-z0-9_\x7f-\xff ]/", '', $className);
        }
        return $className;
    }

    /**
     * Returns the code of a "service provider helper class" that contains generated factory code.
     *
     * @return string
     */
    private function dumpServiceProviderHelper(string $className): string
    {
        $slashPos = strrpos($className, '\\');
        if ($slashPos !== false) {
            $namespace = 'namespace '.substr($className, 0, $slashPos).";\n";
            $shortClassName = substr($className, $slashPos+1);
        } else {
            $namespace = null;
            $shortClassName = $className;
        }

        $factoriesArrayCode = [];
        $factories = [];
        $factoryCount = 0;
        foreach ($this->getFactoryDefinitions() as $definition) {
            if ($definition->isPsrFactory()) {
                $factoriesArrayCode[] = '            '.var_export($definition->getName(), true).' => ['.var_export($definition->getReflectionMethod()->getDeclaringClass()->getName(), true).', '.var_export($definition->getReflectionMethod()->getName(), true)."],\n";
            } else {
                $factoryCount++;
                $localFactoryName = 'factory'.$factoryCount;
                $factoriesArrayCode[] = '            '.var_export($definition->getName(), true).' => [self::class, '.var_export($localFactoryName, true)."],\n";
                $factories[] = $definition->buildFactoryCode($localFactoryName);
            }
            foreach ($definition->getAliases() as $alias) {
                $factoriesArrayCode[] = '            '.var_export($alias, true).' => new Alias('.var_export($definition->getName(), true)."),\n";
            }
        }

        $factoriesArrayStr = implode("\n", $factoriesArrayCode);
        $factoriesStr = implode("\n", $factories);

        $code = <<<EOF
<?php
$namespace

use Interop\Container\Factories\Alias;
use Psr\Container\ContainerInterface;

final class $shortClassName
{
    public static function getFactories(): array
    {
        return [
$factoriesArrayStr
        ];
    }
    
$factoriesStr
}
EOF;

        return $code;
    }

    /**
     * Returns a list of all container entries extended by this service provider.
     *
     * - the key is the entry name
     * - the value is a callable that will return the modified entry
     *
     * Callables have the following signature:
     *        function(Psr\Container\ContainerInterface $container, $previous)
     *     or function(Psr\Container\ContainerInterface $container, $previous = null)
     *
     * About factories parameters:
     *
     * - the container (instance of `Psr\Container\ContainerInterface`)
     * - the entry to be extended. If the entry to be extended does not exist and the parameter is nullable,
     *   `null` will be passed.
     *
     * @return callable[]
     */
    public function getExtensions()
    {
        // TODO: Implement getExtensions() method.
    }
}
