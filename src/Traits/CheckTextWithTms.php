<?php

namespace Overtrue\LaravelQcloudContentAudit\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Overtrue\LaravelQcloudContentAudit\Moderators\Tms;

trait CheckTextWithTms
{
    //    protected array $tmsCheckable = [];
    //    protected string $tmsCheckStrategy = Tms::DEFAULT_STRATEGY;

    public static function bootCheckTextWithTms(): void
    {
        static::saving(
            function (Model $model) {
                /* @var Model|static $model */
                if (! $model->shouldCheckTextWithTms()) {
                    return;
                }

                foreach ($model->getTmsContents() as $contents) {
                    $contents = preg_replace('/\s+/', '', $contents);

                    if (mb_strlen($contents) < 1) {
                        continue;
                    }

                    $slices = mb_str_split($contents, 3000);

                    foreach ($slices as $slice) {
                        \Overtrue\LaravelQcloudContentAudit\Tms::validate($slice, $model->tmsCheckStrategy ?? Tms::DEFAULT_STRATEGY);
                    }
                }
            }
        );
    }

    public function getTmsCheckableAttributes(): array
    {
        return property_exists($this, 'tmsCheckable') ? $this->tmsCheckable : [];
    }

    public function getTmsCheckStrategy(): string
    {
        return property_exists($this, 'tmsCheckStrategy') ? $this->tmsCheckStrategy : Tms::DEFAULT_STRATEGY;
    }

    public function shouldCheckTextWithTms(): bool
    {
        if (empty($this->getTmsCheckableAttributes())) {
            return false;
        }

        return Arr::hasAny($this->getDirty(), $this->getTmsCheckableAttributes());
    }

    public function getTmsContents(): array
    {
        /* @var Model|static $this */
        $attributes = Arr::only($this->getDirty(), $this->getTmsCheckableAttributes());

        $formattedAttributes = [];

        foreach ($attributes as $attribute) {
            if (! is_string($attribute) && ! is_array($attribute)) {
                continue;
            }

            $formattedAttributes[] = is_array($attribute) ? json_encode($attribute, JSON_UNESCAPED_UNICODE) : $attribute;
        }

        return ($this->tmsJoinFields ?? true) ? [\implode('|', $formattedAttributes)] : $formattedAttributes;
    }
}
