<?php

namespace Overtrue\LaravelQcloudContentAudit\Traits;

use Illuminate\Database\Eloquent\Model;
use Overtrue\LaravelQcloudContentAudit\Events\ModelAttributeTextMasked;
use Overtrue\LaravelQcloudContentAudit\Moderators\Tms;

trait MaskTextWithTms
{
//    protected array $tmsMaskable = [];
//    protected string $tmsMaskStrategy = Tms::DEFAULT_STRATEGY;

    public static function bootMaskTextWithTms()
    {
        static::saving(
            function (Model $model) {
                /* @var Model|static $model */
                if (empty($model->tmsMaskable ?? []) || !self::shouldMaskTextWithTms()) {
                    return;
                }

                foreach ($model->tmsMaskable as $attribute) {
                    $contents = $model->$attribute;

                    if ($model->isClean($attribute) || (! is_string($contents) && ! is_array($contents))) {
                        continue;
                    }

                    $isArrayable = is_array($contents);

                    if ($isArrayable) {
                        $contents = json_encode($contents, JSON_UNESCAPED_UNICODE);
                    }

                    if (mb_strlen(preg_replace('/\s+/', '', $contents)) < 1) {
                        continue;
                    }

                    $slices = mb_str_split($contents, 3000);

                    $result = '';

                    foreach ($slices as $slice) {
                        $result .= \Overtrue\LaravelQcloudContentAudit\Tms::mask($slice, $model->tmsMaskStrategy ?? Tms::DEFAULT_STRATEGY);
                    }

                    if ($isArrayable) {
                        $result = json_decode($result, true);
                    }

                    $model->$attribute = $result;

                    if ($result !== $contents) {
                        \event(new ModelAttributeTextMasked($model, $attribute, $result, $contents));
                    }
                }
            }
        );
    }

    public static function shouldMaskTextWithTms(): bool
    {
        return true;
    }
}
