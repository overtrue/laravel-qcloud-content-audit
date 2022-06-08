<?php

namespace Overtrue\LaravelQcloudContentAudit\Events;

use Illuminate\Database\Eloquent\Model;

class ModelAttributeTextMasked
{
    public Model $model;
    public string $attribute;

    public function __construct(Model $model, string $attribute)
    {
        $this->model = $model;
        $this->attribute = $attribute;
    }
}
