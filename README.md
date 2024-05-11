# 腾讯云内容安全（文字图片内容审核）服务

---

[![CI](https://github.com/overtrue/Laravel-qcloud-content-audit/actions/workflows/ci.yml/badge.svg)](https://github.com/overtrue/Laravel-qcloud-content-audit/actions/workflows/ci.yml)
[![Latest Stable Version](https://poser.pugx.org/overtrue/Laravel-qcloud-content-audit/v/stable.svg)](https://packagist.org/packages/overtrue/Laravel-qcloud-content-audit) 
[![Latest Unstable Version](https://poser.pugx.org/overtrue/Laravel-qcloud-content-audit/v/unstable.svg)](https://packagist.org/packages/overtrue/Laravel-qcloud-content-audit) 
[![Total Downloads](https://poser.pugx.org/overtrue/Laravel-qcloud-content-audit/downloads)](https://packagist.org/packages/overtrue/Laravel-qcloud-content-audit) 
[![License](https://poser.pugx.org/overtrue/Laravel-qcloud-content-audit/license)](https://packagist.org/packages/overtrue/Laravel-qcloud-content-audit)

T-Sec 天御内容安全服务使用了深度学习技术，识别文本/图片中出现的可能令人反感、不安全或不适宜内容，支持用户配置词库/图片黑名单，识别自定义的识别类型。

- :book: [TMS 官方 API 文档](https://cloud.tencent.com/product/tms)
- :book: [IMS 官方 API 文档](https://cloud.tencent.com/product/ims)

[![Sponsor me](https://github.com/overtrue/overtrue/blob/master/sponsor-me-button-s.svg?raw=true)](https://github.com/sponsors/overtrue)

## Installing

```shell
$ composer require overtrue/Laravel-qcloud-content-audit -vvv
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
        
        // 可选，默认使用腾讯云默认策略
        'biz_type' => env('TMS_BIZ_TYPE'), 
        // 可选，开启后跳过 tms 识别/打码功能
        'dry' => env('TMS_DRY', false),
    ],
    
    // 图片审核/识别服务
    'ims' => [
        'secret_id' => env('IMS_SECRET_ID'),
        'secret_key' => env('IMS_SECRET_KEY'),
        'endpoint' => env('IMS_ENDPOINT'),
        
        // 可选，默认使用腾讯云默认策略
        'biz_type' => env('IMS_BIZ_TYPE'),
        // 可选，开启后跳过 ims 识别功能
        'dry' => env('IMS_DRY', false),
    ],
```

## API

### 获取检查结果

调用对应 API 返回数组结果，返回值结构请参考官方 API 文档。

#### 文本

> 接口请求频率限制：1000次/秒。

```php
use Overtrue\LaravelQcloudContentAudit\Tms;

array Tms::check(string $input);
```

#### 图片

> - 接口请求频率限制：100次/秒。
> - 图片检测接口为图片文件内容，大小不能超过5M
> - 图片将会缩放成 300*300 后检查

```php
use Overtrue\LaravelQcloudContentAudit\Ims;

array Ims::check(string $contents);
```
> [!TIP]
>
> `$contents` 可以为：图片内容、图片本地路径或 URL。

### 检查并返回是否通过

```php
use Overtrue\LaravelQcloudContentAudit\Tms;
use Overtrue\LaravelQcloudContentAudit\Ims;

bool Tms::validate(string $contents, string $strategy = 'strict')
bool Ims::validate(string $contents, string $strategy = 'strict')
```

### 直接替换敏感文本内容

直接将检测到的敏感词替换为 `*`：

```php
use Overtrue\LaravelQcloudContentAudit\Tms;

string Tms::mask(string $input, string $char = '*', string $strategy = 'strict');

// 示例：
echo Tms::mask('这是敏感内容哦'); 
// "这是**哦"
```

## 在模型中使用

### 文本校验（CheckTextWithTms）

> [!WARNING]
>
> 此操作为同步，可能会影响接口性能，谨慎使用

```php
use Illuminate\Database\Eloquent\Model;
use Overtrue\LaravelQcloudContentAudit\Traits\CheckTextWithTms;

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

异步检测到敏感内容时替换为 * 号。

```php
use Illuminate\Database\Eloquent\Model;
use Overtrue\LaravelQcloudContentAudit\Traits\MaskTextWithTms;

class Post extends Model 
{
    use MaskTextWithTms;
    
    protected $tmsMaskable = ['name', 'description'];
    // protected $tmsMaskStrategy = 'strict'; // 开启打码的策略情况，可选，默认使用最严格模式
    
    //...
}
```


> [!WARNING]
> 
> 此行为为异步，默认监听模型 `saved` 事件，触发 `MaskModelAttributes::dispatch($model)`，如需禁用此行为，可如下设置：
> ```php
> protected bool $tmsMaskOnSaved = false;
> ```

## 使用表单校验规则

您可以在表单验证时使用 `tms` 或者 `tms:{strategy}` 模式来进行表单验证：

```php
$this->validate($request, [
	'name' => 'required|tms',   // 使用默认 strict 策略
	'avatar' => 'required|url|ims', // 使用默认 strict 策略
	'description' => 'required|tms:strict', // 使用指定策略
	'logo_url' => 'required|url|ims:logo',  // 使用指定策略
]);
```

## 配置策略

你可以通过以下方式注册一个或多个自定义校验规则，决定是否通过校验：

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

> [!TIP]
> 
>- 接口返回值中 Suggestion 有三种返回值：Block：建议屏蔽，Review ：建议人工复审，Pass：建议通过，
>- 另外还有一个 Score，该字段用于返回当前标签（Label）下的置信度，取值范围：0（置信度最低）-100（置信度最高 ），越高代表文本越有可能属于当前返回的标签；如：色情 99，则表明该文本非常有可能属于色情内容；色情 0，则表明该文本不属于色情内容
>

### Events

当文字被检测敏感并打码的时候，将会触发事件：

`Overtrue\LaravelQcloudContentAudit\Events\ModelAttributeTextMasked`

你可以监听该事件，以获取检测结果：

- `$event->origin` 检测前的原始内容，如 `这是敏感内容`
- `$event->result`  打码后的结果，如 `这是**内容`
- `$event->model`   模型对象
- `$event->attribute` 模型属性名，如 `name`

## 异常处理

验证失败将抛出以下异常：

- `Overtrue\LaravelQcloudContentAudit\InvalidTextException`
    - `$contents` - (string) 被检测的文本内容
    - `$response` - (array) API 原始返回值
- `Overtrue\LaravelQcloudContentAudit\InvalidImageException`
    - `$response` - (array) API 原始返回值

## :heart: Sponsor me 

如果你喜欢我的项目并想支持它，[点击这里 :heart:](https://github.com/sponsors/overtrue)

## Project supported by JetBrains

Many thanks to Jetbrains for kindly providing a license for me to work on this and other open-source projects.

[![](https://resources.jetbrains.com/storage/products/company/brand/logos/jb_beam.svg)](https://www.jetbrains.com/?from=https://github.com/overtrue)


## Contributing

You can contribute in one of three ways:

1. File bug reports using the [issue tracker](https://github.com/overtrue/laravel-package/issues).
2. Answer questions or fix bugs on the [issue tracker](https://github.com/overtrue/laravel-package/issues).
3. Contribute new features or update the wiki.

_The code contribution process is not very formal. You just need to make sure that you follow the PSR-0, PSR-1, and PSR-2 coding guidelines. Any
new code contributions must be accompanied by unit tests where applicable._

## PHP 扩展包开发

> 想知道如何从零开始构建 PHP 扩展包？
>
> 请关注我的实战课程，我会在此课程中分享一些扩展开发经验 —— [《PHP 扩展包实战教程 - 从入门到发布》](https://learnku.com/courses/creating-package)

## License

MIT
