<?php

namespace Tests\Traits;

use Illuminate\Database\Eloquent\Model;
use Overtrue\LaravelQcloudContentAudit\Tms;
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
    public function test_it_can_mask_attributes_after_model_saved()
    {
        Tms::shouldReceive('mask')
            ->with(['name' => '这是敏感内容啊'], \Overtrue\LaravelQcloudContentAudit\Moderators\Tms::DEFAULT_STRATEGY)
            ->andReturn(['name' => '这是**啊'])
            ->once();

        $user = new class extends Model
        {
            use MaskTextWithTms;

            protected $table = 'users';

            protected array $tmsMaskable = ['name', 'description'];

            protected $fillable = ['name', 'description'];
        };

        $user->fill(['name' => '这是敏感内容啊']);
        $user->save();

        $user->refresh();

        $this->assertSame('这是**啊', $user->name);
    }

    public function test_it_can_mask_multi_attributes_after_model_saved()
    {
        Tms::shouldReceive('mask')
            ->with([
                'name' => '这是敏感内容啊',
                'description' => '这还是敏感内容啊',
                'settings' => [
                    'key1' => '这是敏感内容啊',
                    'key2' => '这还是敏感内容啊',
                ],
            ],
                \Overtrue\LaravelQcloudContentAudit\Moderators\Tms::DEFAULT_STRATEGY
            )
            ->andReturnUsing(function ($contents, $strategy) {
                return json_decode(str_replace('敏感', '**', json_encode($contents, JSON_UNESCAPED_UNICODE)), true);
            })
            ->once();

        $user = new class extends Model
        {
            use MaskTextWithTms;

            protected $table = 'users';

            protected $tmsMaskable = ['name', 'description', 'settings'];

            protected $casts = [
                'settings' => 'array',
            ];
        };

        $user->name = '这是敏感内容啊';
        $user->description = '这还是敏感内容啊';
        $user->settings = ['key1' => '这是敏感内容啊',  'key2' => '这还是敏感内容啊'];
        $user->save();
        $user->refresh();

        $this->assertSame('这是**内容啊', $user->name);
        $this->assertSame('这还是**内容啊', $user->description);
        $this->assertSame(['key1' => '这是**内容啊',  'key2' => '这还是**内容啊'], $user->settings);
    }
}
