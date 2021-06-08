<?php

namespace Tests;

use Illuminate\Foundation\Testing\Concerns\InteractsWithContainer;
use Intervention\Image\Facades\Image;
use Mockery\MockInterface;
use Overtrue\LaravelQcs\Exceptions\InvalidImageException;
use Overtrue\LaravelQcs\Ims;
use TencentCloud\Ims\V20201229\Models\ImageModerationRequest;
use TencentCloud\Ims\V20201229\Models\ImageModerationResponse;

class ImsTest extends TestCase
{
    use InteractsWithContainer;

    public function test_is_can_check_image_contents()
    {
        $response = new ImageModerationResponse();
        $response->deserialize(
            [
                'Suggestion' => 'Pass',
            ]
        );
        $imagePath = __DIR__ . '/images/500x500.png';
        $imageContents = \file_get_contents($imagePath);

        $this->instance(
            'ims-service',
            \Mockery::mock(
                'stdClass',
                function (MockInterface $service) use ($response) {
                    $service->shouldReceive('ImageModeration')->with(
                        \Mockery::on(
                            function (ImageModerationRequest $request) {
                                $img = Image::make($request->getFileContent());

                                $this->assertSame(\Overtrue\LaravelQcs\Moderators\Ims::MAX_SIZE, $img->getWidth());
                                $this->assertSame(\Overtrue\LaravelQcs\Moderators\Ims::MAX_SIZE, $img->getHeight());

                                return true;
                            }
                        )
                    )
                        ->andReturn($response);
                }
            )
        );

        // using path
        $this->assertSame(['Suggestion' => 'Pass',], Ims::check($imagePath));

        // using contents
        $this->assertSame(['Suggestion' => 'Pass',], Ims::check($imageContents));
    }

    public function test_it_can_validate_image_contents()
    {
        $response = new ImageModerationResponse();
        $response->deserialize(
            [
                'Suggestion' => 'Review',
            ]
        );
        $imagePath = __DIR__ . '/images/500x500.png';
        $imageContents = \file_get_contents($imagePath);
        $this->instance(
            'ims-service',
            \Mockery::mock(
                'stdClass',
                function (MockInterface $service) use ($response, $imageContents) {
                    $service->shouldReceive('ImageModeration')->with(
                        \Mockery::on(
                            function (ImageModerationRequest $request) use ($imageContents) {
                                $img = Image::make($request->getFileContent());

                                $this->assertSame(\Overtrue\LaravelQcs\Moderators\Ims::MAX_SIZE, $img->getWidth());
                                $this->assertSame(\Overtrue\LaravelQcs\Moderators\Ims::MAX_SIZE, $img->getHeight());

                                return true;
                            }
                        )
                    )
                        ->andReturn($response);
                }
            )
        );

        $this->expectException(InvalidImageException::class);

        Ims::validate($imageContents);
    }

    public function test_it_can_validate_image_contents_with_custom_strategy()
    {
        $response = new ImageModerationResponse();
        $response->deserialize(
            [
                'Suggestion' => 'Review',
            ]
        );
        $imagePath = __DIR__ . '/images/500x500.png';
        $this->instance(
            'ims-service',
            \Mockery::mock(
                'stdClass',
                function (MockInterface $service) use ($response) {
                    $service->shouldReceive('ImageModeration')->with(
                        \Mockery::on(
                            function (ImageModerationRequest $request) {
                                $img = Image::make($request->getFileContent());

                                $this->assertSame(\Overtrue\LaravelQcs\Moderators\Ims::MAX_SIZE, $img->getWidth());
                                $this->assertSame(\Overtrue\LaravelQcs\Moderators\Ims::MAX_SIZE, $img->getHeight());

                                return true;
                            }
                        )
                    )
                        ->andReturn($response);
                }
            )
        );

        Ims::setStrategy('review', fn ($result) => $result['Suggestion'] === 'Review');

        $this->assertTrue(Ims::validate($imagePath, 'review'));
    }
}
