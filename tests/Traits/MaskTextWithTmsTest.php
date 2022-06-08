<?php

namespace Tests\Traits;

use Illuminate\Database\Eloquent\Model;
use Overtrue\LaravelQcloudContentAudit\Traits\MaskTextWithTms;
use Tests\TestCase;

class UserWithMaskTextTrait extends Model
{
    use MaskTextWithTms;

    protected $table = 'users';
    protected array $tmsMaskable = ['name'];
    protected $fillable = ['name'];
}


class MaskTextWithTmsTest extends TestCase
{
    public function test_it_can_mask_attributes_on_model_saving()
    {
        \Overtrue\LaravelQcloudContentAudit\Tms::shouldReceive('mask')
            ->with('这是敏感内容啊', \Overtrue\LaravelQcloudContentAudit\Moderators\Tms::DEFAULT_STRATEGY)
            ->andReturn('这是**啊');

        $user = new UserWithMaskTextTrait(['name' => '这是敏感内容啊']);

        $user->save();

        $this->assertSame('这是**啊', $user->name);
    }
}
