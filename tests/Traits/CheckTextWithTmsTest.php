<?php

namespace Tests\Traits;

use Illuminate\Database\Eloquent\Model;
use Mockery\MockInterface;
use Overtrue\LaravelQcs\Exceptions\InvalidTextException;
use Overtrue\LaravelQcs\Traits\CheckTextWithTms;
use TencentCloud\Tms\V20201229\Models\TextModerationResponse;
use Tests\TestCase;

class UserWithCheckTextTrait extends Model
{
    use CheckTextWithTms;
    protected $table = 'users';

    protected array $tmsCheckable = ['name'];
    protected array $tmsMaskable = ['description'];

    protected $fillable = ['name', 'description'];
}


class CheckTextWithTmsTest extends TestCase
{
    public function test_it_can_check_attributes_on_model_saving()
    {
        $response = new TextModerationResponse();
        $response->deserialize(
            [
                'Suggestion' => 'Review',
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

        $user = new UserWithCheckTextTrait(['name' => '敏感内容']);

        $this->expectException(InvalidTextException::class);

        $user->save();
    }
}
