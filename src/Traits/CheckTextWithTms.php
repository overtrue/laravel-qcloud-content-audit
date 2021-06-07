<?php

namespace Overtrue\LaravelQcs\Traits;

use Illuminate\Database\Eloquent\Model;
use Overtrue\LaravelQcs\Moderators\Tms;

trait CheckTextWithTms
{
//    protected array $tmsCheckable = [];
//    protected string $tmsCheckStrategy = Tms::DEFAULT_STRATEGY;
//    protected bool $tmsJoinFields = false;

    public static function bootCheckTextWithTms()
    {
        static::saving(
            function (Model $model) {
                /* @var Model|static $model */
                if (empty($model->tmsCheckable ?? [])) {
                    return;
                }

                foreach ($model->getTmsContents() as $content) {
                    app('tms')->validate($content, $model->tmsCheckStrategy ?? Tms::DEFAULT_STRATEGY);
                }
            }
        );
    }

    public function getTmsContents(): array
    {
        /* @var Model|static $this */
        $attributes = $this->only($this->tmsCheckable ?? []);

        return ($this->tmsJoinFields ?? true) ? [\join('|', $attributes)] : $attributes;
    }
}
