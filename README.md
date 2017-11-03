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
     * @Factory
     * @Tag(name="middlewares.queue", priority=MiddlewareOrder::EXCEPTION_EARLY)
     */
    public static function createMiddleware() : WhoopsMiddleware
    {
        return new WhoopsMiddleware();
    }
}
```

Funky implements the `getFactories` and `getExtensions` methods.

Your class simply extends `Funky\ServiceProvider`. Funky will scan your class for `@Factory` and `@Extension` annotations.

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
