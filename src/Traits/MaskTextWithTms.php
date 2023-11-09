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
                $model->maskModelAttributes();
            }
        );
    }

    public static function shouldMaskTextWithTms(): bool
    {
        return true;
    }

    public function maskContentsWithTms(string|array $input): mixed
    {
        $contents = $input;
        $isArrayable = is_array($contents);

        if ($isArrayable) {
            $contents = json_encode($contents, JSON_UNESCAPED_UNICODE);
        }

        if (mb_strlen(preg_replace('/\s+/', '', $contents)) < 1) {
            return $contents;
        }

        $slices = mb_str_split($contents, 3000);

        $result = '';

        foreach ($slices as $slice) {
            $result .= \Overtrue\LaravelQcloudContentAudit\Tms::mask($slice, $model->tmsMaskStrategy ?? Tms::DEFAULT_STRATEGY);
        }

        if ($isArrayable) {
            $result = json_decode($result, true);
        }

        return json_last_error() !== 0 ? $input : $result;
    }

    public function maskModelAttributes(): void
    {
        /* @var Model|static $this */
        if (empty($this->tmsMaskable ?? []) || ! self::shouldMaskTextWithTms()) {
            return;
        }

        foreach ($this->tmsMaskable as $attribute) {
            $contents = $this->$attribute;

            if ($this->isClean($attribute) || (! is_string($contents) && ! is_array($contents))) {
                continue;
            }

            $result = $this->maskContentsWithTms($contents);

            $masked = $result !== $contents;

            $this->$attribute = $result;

            if ($masked) {
                \event(new ModelAttributeTextMasked($this, $attribute, $result, $contents));
            }
        }
    }
}
