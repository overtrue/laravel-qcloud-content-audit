<?php

namespace Overtrue\LaravelQcs\Rules;

use Illuminate\Contracts\Validation\Rule;

class Tms implements Rule
{
    protected ?string $strategy = null;

    public function __construct(string $strategy = \Overtrue\LaravelQcs\Moderators\Tms::DEFAULT_STRATEGY)
    {
        $this->strategy = $strategy;
    }

    public function passes($attribute, $value)
    {
        try {
            return app('tms')->validate($value, $this->strategy);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function message()
    {
        return 'The :attribute contains illegal content.';
    }
}
