Laravel Package
---

Laravel package template.

## Installing

```shell
$ composer require overtrue/laravel-package -vvv
```

### Migrations

This step is also optional, if you want to custom the pivot table, you can publish the migration files:

```php
$ php artisan vendor:publish --provider="Overtrue\\LaravelPackage\\PackageServiceProvider" --tag=migrations
```

## Usage

TODO

### Events

| **Event**                                       | **Description**                             |
| ----------------------------------------------- | ------------------------------------------- |
| `Overtrue\LaravelPackage\Events\SampleEvent`    | Sample description.                         |

## Contributing

You can contribute in one of three ways:

1. File bug reports using the [issue tracker](https://github.com/overtrue/laravel-package/issues).
2. Answer questions or fix bugs on the [issue tracker](https://github.com/overtrue/laravel-package/issues).
3. Contribute new features or update the wiki.

_The code contribution process is not very formal. You just need to make sure that you follow the PSR-0, PSR-1, and PSR-2 coding guidelines. Any new code contributions must be accompanied by unit tests where applicable._

## PHP 扩展包开发

> 想知道如何从零开始构建 PHP 扩展包？
>
> 请关注我的实战课程，我会在此课程中分享一些扩展开发经验 —— [《PHP 扩展包实战教程 - 从入门到发布》](https://learnku.com/courses/creating-package)

## License

MIT
