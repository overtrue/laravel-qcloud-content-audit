<?php

namespace Tests\Traits;

use Illuminate\Database\Eloquent\Model;
use Overtrue\LaravelQcs\Exceptions\InvalidTextException;
use Overtrue\LaravelQcs\Traits\CheckTextWithTms;
use Tests\TestCase;

class UserWithCheckTextTrait extends Model
{
    use CheckTextWithTms;

    protected $table = 'users';
    protected array $tmsCheckable = ['name'];
    protected $fillable = ['name', 'description'];
}


class CheckTextWithTmsTest extends TestCase
{
    public function test_it_can_check_attributes_on_model_saving()
    {
        \Overtrue\LaravelQcs\Tms::shouldReceive('validate')
            ->with('敏感内容', \Overtrue\LaravelQcs\Moderators\Tms::DEFAULT_STRATEGY)
            ->andThrow(new InvalidTextException('敏感内容', []));

        $user = new UserWithCheckTextTrait(['name' => '敏感内容']);

        $this->expectException(InvalidTextException::class);

        $user->save();
    }
}
