<?php

namespace Overtrue\LaravelQcloudContentAudit\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Overtrue\LaravelQcloudContentAudit\Events\ModelAttributeTextMasked;
use Overtrue\LaravelQcloudContentAudit\Tms;

class MaskModelAttributes implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @param  \Illuminate\Database\Eloquent\Model|\Overtrue\LaravelQcloudContentAudit\Traits\MaskTextWithTms  $model
     */
    public function __construct(public Model $model, public array $attributes = [])
    {
        //
    }

    public function handle(): void
    {
        $attributes = $this->attributes;

        if (empty($attributes)) {
            $attributes = $this->model->getTmsMaskableAttributes();
        }

        $contents = [];

        foreach ($attributes as $attribute) {
            $value = $this->model->getAttribute($attribute);

            if (! is_string($value) && ! is_array($value)) {
                continue;
            }

            $contents[$attribute] = $value;
        }

        $result = Tms::mask($contents, $this->model->getTmsMaskStrategy());

        if (! empty($result) && is_array($result)) {
            foreach ($result as $key => $value) {
                $this->model->setAttribute($key, $value);
            }
        }

        foreach ($attributes as $attribute) {
            if ($this->model->isDirty($attribute)) {
                event(new ModelAttributeTextMasked($this->model, $attribute, $result[$attribute], $contents[$attribute]));
            }
        }

        $this->model->withoutEvents(function () {
            $this->model->save();
        });
    }
}
