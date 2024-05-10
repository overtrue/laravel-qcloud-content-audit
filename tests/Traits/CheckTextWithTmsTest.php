<?php

namespace Tests\Traits;

use Illuminate\Database\Eloquent\Model;
use Overtrue\LaravelQcloudContentAudit\Exceptions\InvalidTextException;
use Overtrue\LaravelQcloudContentAudit\Traits\CheckTextWithTms;
use Tests\TestCase;

class UserWithCheckTextTrait extends Model
{
    use CheckTextWithTms;

    protected $table = 'users';

    protected array $tmsCheckable = ['name', 'description'];

    protected $fillable = ['name', 'description'];
}

class CheckTextWithTmsTest extends TestCase
{
    public function test_it_can_check_attributes_on_model_saving()
    {
        \Overtrue\LaravelQcloudContentAudit\Tms::shouldReceive('validate')
            ->with('敏感内容', \Overtrue\LaravelQcloudContentAudit\Moderators\Tms::DEFAULT_STRATEGY)
            ->andThrow(new InvalidTextException('Invalid text', '敏感内容', []));

        $user = new UserWithCheckTextTrait(['name' => '敏感内容']);

        $this->expectException(InvalidTextException::class);

        $user->save();
    }
}
