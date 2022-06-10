<?php

namespace Overtrue\LaravelQcloudContentAudit\Events;

use Illuminate\Database\Eloquent\Model;

class ModelAttributeTextMasked
{
    public function __construct(
        public Model $model,
        public string $attribute,
        public string $result,
        public string $origin
    ) {
    }
}
