# PHP cache compiler

This is currently in development stage, and deactivated.

## How to work on it

You have to disable the old compiler, and enable the new one.
Please follow these steps:

- **PHP\Compiler**: uncomment `//(new Cache\Compiler)->compile();`
- **config.php**:

**uncomment**
```php
Compiler::class => [],
```

**comment**
```php
Compiler::class => [
    1 => [Router::class, Builder\Compiler::class],
    2 => [Builder\Linked_Classes_Compiler::class],
    3 => [AOP\Compiler::class],
    4 => [Mysql\Compiler::class]
],
```
