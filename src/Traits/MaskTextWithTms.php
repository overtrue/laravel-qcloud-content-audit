<?php

namespace Overtrue\LaravelQcs\Traits;

use Illuminate\Database\Eloquent\Model;
use Overtrue\LaravelQcs\Events\ModelAttributeTextMasked;
use Overtrue\LaravelQcs\Moderators\Tms;

trait MaskTextWithTms
{
//    protected array $tmsMaskable = [];
//    protected string $tmsMaskStrategy = Tms::DEFAULT_STRATEGY;

    public static function bootMaskTextWithTms()
    {
        static::saving(
            function (Model $model) {
                /* @var Model|static $model */
                if (empty($model->tmsMaskable ?? [])) {
                    return;
                }

                foreach ($model->tmsMaskable as $attribute) {
                    $contents = $model->$attribute;
                    $model->$attribute = app('tms')->mask($contents, $model->tmsMaskStrategy ?? Tms::DEFAULT_STRATEGY);

                    if ($model->$attribute !== $contents) {
                        \event(new ModelAttributeTextMasked($model, $attribute));
                    }
                }
            }
        );
    }
}
