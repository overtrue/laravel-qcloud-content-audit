Laravel Qcloud Content Security
---

T-Sec 天御内容安全服务使用了深度学习技术，识别文本/图片中出现的可能令人反感、不安全或不适宜内容，支持用户配置词库/图片黑名单，识别自定义的识别类型。

- :book: [TMS 官方 API 文档](https://cloud.tencent.com/product/tms)
- :book: [IMS 官方 API 文档](https://cloud.tencent.com/product/ims)

## Installing

```shell
$ composer require overtrue/laravel-qcs -vvv
```

### Config

请在 `config/services.php` 中配置以下内容：

```php
    //...
    // 文字识别服务
    'tms' => [
        'secret_id' => env('TMS_SECRET_ID'),
        'secret_key' => env('TMS_SECRET_KEY'),
        'endpoint' => env('TMS_ENDPOINT'),
    ],
    
    // 图片审核/识别服务
    'ims' => [
        'secret_id' => env('IMS_SECRET_ID'),
        'secret_key' => env('IMS_SECRET_KEY'),
        'endpoint' => env('IMS_ENDPOINT'),
    ],
```

## API
### 获取检查结果

调用对应 API 返回数组结果，返回值结构请参考官方 API 文档。

#### 文本

> 接口请求频率限制：1000次/秒。

```php
use Overtrue\LaravelQcs\Tms;

array Tms::check(string $input);
```

#### 图片

> - 接口请求频率限制：100次/秒。
> - 图片检测接口为图片文件内容，大小不能超过5M
> - 图片将会缩放成 300*300 后检查

```php
use Overtrue\LaravelQcs\Ims;

array Ims::check(string $pathOrUrl);
```

### 直接替换敏感文本内容

直接将检测到的敏感词替换为 `*`：

```php
use Overtrue\LaravelQcs\Tms;

string Tms::mask(string $input, string $char = '*', string $strategy = 'strict');

// 示例：
echo Tms::mask('这是敏感内容哦'); 
// "这是**哦"
```


## 在模型中使用

### 文本校验（CheckTextWithTms）

```php
use Illuminate\Database\Eloquent\Model;
use Overtrue\LaravelQcs\Traits\CheckTextWithTms;

class Post extends Model 
{
    // 文本校验
    use CheckTextWithTms;
    
    protected array $tmsCheckable = ['name', 'description'];
    protected string $tmsCheckStrategy = 'strict'; // 可选，默认使用最严格模式
    
    //...
}
```

### 文本打码（MaskTextWithTms）

检测到敏感内容时不抛出异常，而是替换为 * 号。

```php
use Illuminate\Database\Eloquent\Model;
use Overtrue\LaravelQcs\Traits\MaskTextWithTms;

class Post extends Model 
{
    use MaskTextWithTms;
    
    protected $tmsMaskable = ['name', 'description'];
    protected $tmsMaskStrategy = 'review'; // 开启打码的策略情况，可选，默认使用最严格模式
    
    //...
}
```

## 使用表单校验规则

```php
$this->validate($request, [
	'name' => 'required|tms',
	'avatar' => 'required|url|ims',
	'description' => 'required|tms:strict',
	'logo_url' => 'required|url|ims:logo',
]);
```

## 配置策略
```php
// 文字
Tms::setStrategy('strict', function($result) {
	return $result['Suggestion'] === 'Pass';
});

// 图片
Ims::setStrategy('logo', function($result) {
	return $result['Suggestion'] === 'Pass';
});
```


### Events

| **Event**                                       | **Description**                             |
| ----------------------------------------------- | ------------------------------------------- |
| `Overtrue\LaravelQcs\Events\ModelAttributeTextMasked`    | 模型属性值打码后触发. 可获取 `$model` 和 `$attribute` |

## 异常处理

验证失败将抛出异常：`Overtrue\LaravelQcs\Exception`，你可以获取该验证结果相关信息：

```php
array $exception->result;  	// 验证结果的 API 返回值
array $exception->contents;	// 校验的内容
```

另外还有两个子类：

- `Overtrue\LaravelQcs\InvalidTextException`
- `Overtrue\LaravelQcs\InvalidImageException`

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
