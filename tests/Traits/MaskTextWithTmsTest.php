<?php

namespace Tests\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;
use Overtrue\LaravelQcloudContentAudit\Events\ModelAttributeTextMasked;
use Overtrue\LaravelQcloudContentAudit\Moderators\Tms;
use Overtrue\LaravelQcloudContentAudit\Traits\MaskTextWithTms;
use Tests\TestCase;

class UserWithMaskTextTrait extends Model
{
    use MaskTextWithTms;

    protected $table = 'users';

    protected array $tmsMaskable = ['name', 'description'];

    protected $fillable = ['name', 'description'];
}

class MaskTextWithTmsTest extends TestCase
{
    public function test_it_can_mask_attributes_on_model_saving()
    {
        \Overtrue\LaravelQcloudContentAudit\Tms::shouldReceive('mask')
            ->with('这是敏感内容啊', Tms::DEFAULT_STRATEGY)
            ->andReturn('这是**啊');

        $user = new UserWithMaskTextTrait(['name' => '这是敏感内容啊']);

        $user->save();

        $this->assertSame('这是**啊', $user->name);
    }

    public function test_it_can_mask_multi_attributes_on_model_saving()
    {
        \Overtrue\LaravelQcloudContentAudit\Tms::shouldReceive('mask')
            ->withAnyArgs()
            ->andReturnUsing(function($contents, $strategy){
                return str_replace("敏感", '**', $contents);
            });

        $user = new class extends Model
        {
            use MaskTextWithTms;

            protected $tmsMaskable = ['name', 'description', 'arrayFields'];
        };

        $user->name = '这是敏感内容啊';
        $user->description = '这还是敏感内容啊';
        $user->arrayFields = ['这是敏感内容啊', '这还是敏感内容啊'];

        Event::fake(ModelAttributeTextMasked::class);

        $user->maskModelAttributes();
        $this->assertSame('这是**内容啊', $user->name);
        $this->assertSame('这还是**内容啊', $user->description);
        $this->assertSame(['这是**内容啊', '这还是**内容啊'], $user->arrayFields);
    }
}
