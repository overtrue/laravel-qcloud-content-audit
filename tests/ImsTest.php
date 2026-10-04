<?php

namespace Tests;

use Illuminate\Foundation\Testing\Concerns\InteractsWithContainer;
use Illuminate\Http\UploadedFile;
use Mockery\MockInterface;
use Overtrue\LaravelQcloudContentAudit\Exceptions\InvalidImageException;
use Overtrue\LaravelQcloudContentAudit\Ims;
use PHPUnit\Framework\Attributes\DataProvider;
use TencentCloud\Ims\V20201229\Models\ImageModerationRequest;
use TencentCloud\Ims\V20201229\Models\ImageModerationResponse;

class ImsTest extends TestCase
{
    use InteractsWithContainer;

    public function test_is_can_check_image_contents()
    {
        $response = new ImageModerationResponse;
        $response->deserialize(
            [
                'Suggestion' => 'Pass',
            ]
        );
        $imagePath = __DIR__.'/images/500x500.png';
        $imageContents = \file_get_contents($imagePath);

        $this->instance(
            'ims-service',
            \Mockery::mock(
                'stdClass',
                function (MockInterface $service) use ($response) {
                    $service->shouldReceive('ImageModeration')->with(
                        \Mockery::on(
                            function (ImageModerationRequest $request) {
                                $dimensions = getimagesizefromstring(base64_decode($request->getFileContent()));

                                $this->assertSame(\Overtrue\LaravelQcloudContentAudit\Moderators\Ims::MAX_SIZE, $dimensions[0]);
                                $this->assertSame(\Overtrue\LaravelQcloudContentAudit\Moderators\Ims::MAX_SIZE, $dimensions[1]);

                                return true;
                            }
                        )
                    )
                        ->andReturn($response);
                }
            )
        );

        // using path
        $this->assertSame(['Suggestion' => 'Pass'], Ims::check($imagePath));

        // using contents
        $this->assertSame(['Suggestion' => 'Pass'], Ims::check($imageContents));
    }

    public function test_it_can_validate_image_contents()
    {
        $response = new ImageModerationResponse;
        $response->deserialize(
            [
                'Suggestion' => 'Review',
            ]
        );
        $imagePath = __DIR__.'/images/500x500.png';
        $imageContents = \file_get_contents($imagePath);
        $this->instance(
            'ims-service',
            \Mockery::mock(
                'stdClass',
                function (MockInterface $service) use ($response) {
                    $service->shouldReceive('ImageModeration')->with(
                        \Mockery::on(
                            function (ImageModerationRequest $request) {
                                $dimensions = getimagesizefromstring(base64_decode($request->getFileContent()));

                                $this->assertSame(\Overtrue\LaravelQcloudContentAudit\Moderators\Ims::MAX_SIZE, $dimensions[0]);
                                $this->assertSame(\Overtrue\LaravelQcloudContentAudit\Moderators\Ims::MAX_SIZE, $dimensions[1]);

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
        $response = new ImageModerationResponse;
        $response->deserialize(
            [
                'Suggestion' => 'Review',
            ]
        );
        $imagePath = __DIR__.'/images/500x500.png';
        $this->instance(
            'ims-service',
            \Mockery::mock(
                'stdClass',
                function (MockInterface $service) use ($response) {
                    $service->shouldReceive('ImageModeration')->with(
                        \Mockery::on(
                            function (ImageModerationRequest $request) {
                                $dimensions = getimagesizefromstring(base64_decode($request->getFileContent()));

                                $this->assertSame(\Overtrue\LaravelQcloudContentAudit\Moderators\Ims::MAX_SIZE, $dimensions[0]);
                                $this->assertSame(\Overtrue\LaravelQcloudContentAudit\Moderators\Ims::MAX_SIZE, $dimensions[1]);

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

    public function test_it_can_toggle_validate()
    {
        $response = new ImageModerationResponse;
        $response->deserialize(
            [
                'Suggestion' => 'Review',
            ]
        );
        $imagePath = __DIR__.'/images/500x500.png';
        $imageContents = \file_get_contents($imagePath);
        $this->instance(
            'ims-service',
            \Mockery::mock(
                'stdClass',
                function (MockInterface $service) use ($response) {
                    $service->shouldReceive('ImageModeration')->with(
                        \Mockery::on(
                            function (ImageModerationRequest $request) {
                                $dimensions = getimagesizefromstring(base64_decode($request->getFileContent()));

                                $this->assertSame(\Overtrue\LaravelQcloudContentAudit\Moderators\Ims::MAX_SIZE, $dimensions[0]);
                                $this->assertSame(\Overtrue\LaravelQcloudContentAudit\Moderators\Ims::MAX_SIZE, $dimensions[1]);

                                return true;
                            }
                        )
                    )
                        ->andReturn($response);
                }
            )
        );

        config(['services.ims.dry' => true]);
        $this->assertTrue(Ims::validate($imageContents));

        Ims::dry(false);
        $this->expectException(InvalidImageException::class);
        Ims::validate($imageContents);
    }

    #[DataProvider('imageSizes')]
    public function test_image_resizing_preserves_aspect_ratio(int $width, int $height, int $expectedWidth, int $expectedHeight, string $driver, string $configKey, string $extension = 'png', string $mimeType = 'image/png')
    {
        if (! extension_loaded($driver)) {
            $this->markTestSkipped("The {$driver} extension is required.");
        }

        config([$configKey => $driver]);
        $response = new ImageModerationResponse;
        $response->deserialize(['Suggestion' => 'Pass']);
        $contents = UploadedFile::fake()->image('image.'.$extension, $width, $height)->getContent();

        $this->instance('ims-service', \Mockery::mock('stdClass', function (MockInterface $service) use ($response, $expectedWidth, $expectedHeight, $mimeType) {
            $service->shouldReceive('ImageModeration')->once()->with(\Mockery::on(function (ImageModerationRequest $request) use ($expectedWidth, $expectedHeight, $mimeType) {
                $dimensions = getimagesizefromstring(base64_decode($request->getFileContent()));
                $this->assertSame($expectedWidth, $dimensions[0]);
                $this->assertSame($expectedHeight, $dimensions[1]);
                $this->assertSame($mimeType, $dimensions['mime']);

                return true;
            }))->andReturn($response);
        }));

        $this->assertSame(['Suggestion' => 'Pass'], Ims::check($contents));
    }

    public static function imageSizes(): array
    {
        return [
            'GD landscape' => [600, 300, 300, 150, 'gd', 'image.driver'],
            'GD portrait' => [300, 600, 150, 300, 'gd', 'image.driver'],
            'GD small image' => [100, 50, 300, 150, 'gd', 'image.driver'],
            'Imagick landscape' => [600, 300, 300, 150, 'imagick', 'image.driver'],
            'Imagick portrait' => [300, 600, 150, 300, 'imagick', 'image.driver'],
            'Imagick small image' => [100, 50, 300, 150, 'imagick', 'image.driver'],
            'GD JPEG' => [600, 300, 300, 150, 'gd', 'image.driver', 'jpg', 'image/jpeg'],
            'Imagick JPEG' => [600, 300, 300, 150, 'imagick', 'image.driver', 'jpg', 'image/jpeg'],
            'Laravel image driver' => [600, 300, 300, 150, 'gd', 'images.default'],
        ];
    }
}
