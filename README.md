[![Latest Stable Version](https://poser.pugx.org/thecodingmachine/funky/v/stable)](https://packagist.org/packages/thecodingmachine/funky)
[![Total Downloads](https://poser.pugx.org/thecodingmachine/funky/downloads)](https://packagist.org/packages/thecodingmachine/funky)
[![Latest Unstable Version](https://poser.pugx.org/thecodingmachine/funky/v/unstable)](https://packagist.org/packages/thecodingmachine/funky)
[![License](https://poser.pugx.org/thecodingmachine/funky/license)](https://packagist.org/packages/thecodingmachine/funky)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/thecodingmachine/funky/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/thecodingmachine/funky/?branch=master)
[![Build Status](https://travis-ci.org/thecodingmachine/funky.svg?branch=master)](https://travis-ci.org/thecodingmachine/funky)
[![Coverage Status](https://coveralls.io/repos/thecodingmachine/funky/badge.svg?branch=master&service=github)](https://coveralls.io/github/thecodingmachine/funky?branch=master)

**Work in progress, no stable version yet**

thecodingmachine/funky
======================

Funky is tool to help you write service providers compatible with [container-interop/service-provider](https://github.com/container-interop/service-provider/).

### Without Funky:

The current trend is to directly implement the `ServiceProviderInterface` when writing service providers.

For instance:

```php
class WhoopsMiddlewareServiceProvider implements ServiceProviderInterface
{

    public function getFactories()
    {
        return [
            Middleware::class => [self::class,'createMiddleware'],
        ];
    }

    public function getExtensions()
    {
        return [
            MiddlewareListServiceProvider::MIDDLEWARES_QUEUE => [self::class,'updatePriorityQueue']
        ]
    }

    public static function createMiddleware() : WhoopsMiddleware
    {
        return new WhoopsMiddleware();
    }

    public static function updatePriorityQueue(ContainerInterface $container, \SplPriorityQueue $queue) : \SplPriorityQueue
    {
        $queue->insert($container->get(Middleware::class), MiddlewareOrder::EXCEPTION_EARLY);
        return $queue;
    }
}
```

### With Funky:

```php
class WhoopsMiddlewareServiceProvider extends Funky\ServiceProvider
{
    /**
     * @Factory(
     *   tags={@Tag(name="middlewares.queue", priority=MiddlewareOrder::EXCEPTION_EARLY)}
     * )
     */
    public static function createMiddleware() : WhoopsMiddleware
    {
        return new WhoopsMiddleware();
    }
}
```

Funky implements the `getFactories` and `getExtensions` methods.

Your class simply extends `TheCodingMachine\Funky\ServiceProvider`. Funky will scan your class for `@Factory` and `@Extension` annotations.

## Install

Simply require `thecodingmachine/funky` from your service provider package.

```php
$ composer require thecodingmachine/funky
```

## Usage

Instead of creating a class that implements `Interop\Container\ServiceProviderInterface`, you extend the `TheCodingMachine\Funky\ServiceProvider` class.

## The @Factory annotation

### Default naming

By default, the `@Factory` is using the return type as the name of the container entry

```php
/**
 * @Factory
 */
public static function createMiddleware() : WhoopsMiddleware
{
    return new WhoopsMiddleware();
}
```

```php
$container->get(WhoopsMiddleware::class) // will return the service
```

### Specifying a name

You can use the "name" attribute of the `@Factory` annotation to specify a name:

```php
/**
 * @Factory(name="whoops")
 */
public static function createMiddleware() : WhoopsMiddleware
{
    return new WhoopsMiddleware();
}
```

```php
$container->get('whoops') // will return the service
```

### Using the method name

You can use the "nameFromMethodName" attribute of the `@Factory` annotation to tell Funky to use the method name as an identifier:

```php
/**
 * @Factory(nameFromMethodName=true)
 */
public static function whoopsMiddleware() : WhoopsMiddleware
{
    return new WhoopsMiddleware();
}
```

```php
$container->get('whoopsMiddleware') // will return the service
```

## Auto-wiring

Funky supports auto-wiring! You can simply add parameters with the appropriate type-hint in your factories and Funky will look for this dependencies in the container.

```php
/**
 * @Factory()
 */
public static function myService(LoggerInterface $logger) : MyService
{
    // $logger is fetched from the container using "$container->get(LoggerInterface::class)"
    return new MyService($logger);
}
```

If you do not add type-hints (or use a scalar type-hint), Funky will try to fetch the dependency using the parameter name.

```php
/**
 * @Factory()
 */
public static function myService(string $ROOT_PATH) : MyService
{
    // $ROOT_PATH is fetched from the container using "$container->get('ROOT_PATH')"
    return new MyService(string $ROOT_PATH);
}
```

Finally, at any point, you can also inject the whole container to fetch any dependency from it:

```php
/**
 * @Factory()
 */
public static function myService(ContainerInterface $container) : MyService
{
    return new MyService($container->get('ROOT_PATH'));
}
```


## Extending entries

You can extend entries from the container using the `Extension` annotation.

You should pass the entry to be extended as the first argument. The remaining arguments can be auto-wired just like you do with the factories.

```php
/**
 * @Extension()
 */
public static function registerTwigExtension(\Twig_Environment $twig, MyTwigExtension $extension) : \Twig_Environment
{
    $twig->register($extension);
    return $twig;
}
```

### Specifying a name

You can use the "name" or "nameFromMethodName" attribute of the `@Extension` annotation to specify the name of the entry to be extended:

```
@Extension(name="twig") // The extended entry is named "twig"
```

```php
/**
 * The extended entry is named twig because the method name is "twig"
 * @Extension(name="twig") 
 */
public static function registerExtension(\Twig_Environment $twig, MyTwigExtension $extension) : \Twig_Environment
{
    // ...
}
```


```php
/**
 * The extended entry is named "twig" because the method name is "twig"
 * @Extension(nameFromMethodName=true) 
 */
public static function twig(\Twig_Environment $twig, MyTwigExtension $extension) : \Twig_Environment
{
    // ...
}
```

## Tags

Out of the box, the [container-interop/service-provider](https://github.com/container-interop/service-provider/) does not have a notion of tags. However, you can build entries in your container that are actually an array of services. Those arrays can be regarded as "tags".

Funky offers you an easy way to tag services, using the `@Tag` annotation. This is a great way to remove a lot of boilerplate code!

Here is an example:

```php
/**
 * @Factory(
 *     tags={@Tag(name="twigExtensions")}
 * ) 
 */
public static function myTwigExtension(\Twig_Environment $twig, MyTwigExtension $extension) : \MyTwigExtension
{
    // ...
}
```

This piece of code declares a `\MyTwigExtension` entry, and adds this entry to the `twigExtensions` tag.

Thereafter, you can fetch the tagged services from the container easily.

For instance:

```php
/**
 * Here, the tag "twigExtensions" used in the function above is injected using auto-wiring.  
 * @Factory()
 */
public static function twig(iterable $twigExtensions) : \Twig_Environment
{
    // ...
    $twig = new Twig_Environement(...);
    foreach ($twigExtensions as $twigExtension) {
        $twig->register($twigExtension);
    }
}
```

You can specify an optional priority level for each tagged service:

```php
/**
 * @Factory(
 *     tags={@Tag(name="my.tag", priority=42.1)}
 * ) 
 */
```

Low priority items will appear first. High priority items will appear last.

Note: under the hood, the tagged services are actually `\SplPriorityQueue` objects. Those are iterables, but are not PHP arrays. If you need arrays, you can use the `iterator_to_array` PHP function to cast those in arrays.

## FAQ


### Why the name?

Because the PHP ecosystem loves music (did you notice? Composer, Symfony, ...)
And because Funky takes its roots and inspiration from [bitexpert/disco](https://github.com/bitExpert/disco), a PSR-11 compliant container that also relies on annotations!

### Why do factories need to be *static*?

In the context of service providers, a factory is a function that builds a service based on the parameters it is passed.
If you have done some functional programming, you should consider a factory is a **pure function**.

Given a set of parameters, it will always generate the same result. Therefore, a factory have no need of any object state (the state is contained in the container that is passed in parameter).

Furthermore, compiled containers (like Symfony or PHP-DI) can use the fact that a factory is *public static* to greatly optimize the way they work with the service provider.
By caching the results given by the `getFactories` and `getExtensions` methods, a compiled container can make the overhead of using Funky to nearly 0.

## Troubleshooting

### It says some file cannot be created

Funky needs to generate some PHP files to be fast. Those files will be written in the Funky 'generated' directory (so most of the time, in `vendor/thecodingmachine/funky/generated`).
If Funky is called from Apache, Apache might not have the right to write files in this directory.
You will have to change the rights of this directory to let Apache write in it.

### Some xxxHelper class cannot be autoloaded

As explained above, Funky needs to generate some PHP files to be fast. Those classes are written in the Funky 'generated' directory.
If you used [Composer's autoritative classmap](https://getcomposer.org/doc/articles/autoloader-optimization.md#optimization-level-2-a-authoritative-class-maps) 
(for instance with the `--classmap-authoritative` option), Composer will scan all classes of your project to build
the classmap. Problem: Funky's classes are not yet written! So Composer classmap will miss those classes.
Therefore, when using Funky, you should not use the `--classmap-authoritative` option.
