<?php

namespace Overtrue\LaravelQcloudContentAudit\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Overtrue\LaravelQcloudContentAudit\Jobs\MaskModelAttributes;
use Overtrue\LaravelQcloudContentAudit\Moderators\Tms;

trait MaskTextWithTms
{
    //    protected array $tmsMaskable = [];
    //    protected string $tmsMaskStrategy = Tms::DEFAULT_STRATEGY;
    //    protected bool $tmsMaskOnSaved = true;

    public static function bootMaskTextWithTms(): void
    {
        static::saved(function (Model $model) {
            $shouldDispatch = property_exists($model, 'tmsMaskOnSaved') ? $model->tmsMaskOnSaved : true;

            /* @var Model|static $model */
            if (! $shouldDispatch || ! $model->shouldMaskTextWithTms()) {
                return;
            }

            MaskModelAttributes::dispatch($model);
        });
    }

    public function getTmsMaskableAttributes(): array
    {
        return property_exists($this, 'tmsMaskable') ? (array) $this->tmsMaskable : [];
    }

    public function getTmsMaskStrategy()
    {
        return property_exists($this, 'tmsMaskStrategy') ? $this->tmsMaskStrategy : Tms::DEFAULT_STRATEGY;
    }

    public function shouldMaskTextWithTms(): bool
    {
        if (empty($this->getTmsMaskableAttributes())) {
            return false;
        }

        return Arr::hasAny($this->getDirty(), $this->getTmsMaskableAttributes());
    }
}
