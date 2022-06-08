<?php

namespace Overtrue\LaravelQcloudContentAudit\Rules;

use Illuminate\Contracts\Validation\Rule;

class Tms implements Rule
{
    protected ?string $strategy = null;

    public function __construct(string $strategy = \Overtrue\LaravelQcloudContentAudit\Moderators\Tms::DEFAULT_STRATEGY)
    {
        $this->strategy = $strategy;
    }

    public function passes($attribute, $value)
    {
        try {
            return \Overtrue\LaravelQcloudContentAudit\Tms::validate($value, $this->strategy);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function message()
    {
        return 'The :attribute contains illegal content.';
    }
}
