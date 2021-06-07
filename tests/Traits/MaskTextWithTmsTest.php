<?php

namespace Tests\Traits;

use Illuminate\Database\Eloquent\Model;
use Mockery\MockInterface;
use Overtrue\LaravelQcs\Traits\MaskTextWithTms;
use TencentCloud\Tms\V20201229\Models\TextModerationResponse;
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
        $response = new TextModerationResponse();
        $response->deserialize(
            [
                'Suggestion' => 'Review',
                'Keywords' => ['敏感', '内容']
            ]
        );
        $this->instance(
            'tms-service',
            \Mockery::mock(
                'stdClass',
                function (MockInterface $service) use ($response) {
                    $service->shouldReceive('TextModeration')->withAnyArgs()->andReturn($response);
                }
            )
        );

        $user = new UserWithMaskTextTrait(['name' => '这是敏感内容啊']);

        $user->save();

        $this->assertSame('这是**啊', $user->name);
    }
}
