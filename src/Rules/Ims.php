<?php

namespace Overtrue\LaravelQcloudContentAudit\Rules;

use Illuminate\Contracts\Validation\Rule;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Ims implements Rule
{
    protected ?string $strategy = null;

    public function __construct(string $strategy = \Overtrue\LaravelQcloudContentAudit\Moderators\Ims::DEFAULT_STRATEGY)
    {
        $this->strategy = $strategy;
    }

    public function passes($attribute, $value)
    {
        if (! ($value instanceof UploadedFile)) {
            return false;
        }

        try {
            return \Overtrue\LaravelQcloudContentAudit\Ims::validate($value->getContent(), $this->strategy);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function message()
    {
        return 'The :attribute contains illegal content.';
    }
}
