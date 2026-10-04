<?php

namespace Overtrue\LaravelQcloudContentAudit\Moderators;

use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Intervention\Image\Encoders\AutoEncoder;
use Intervention\Image\ImageManager;
use Overtrue\LaravelQcloudContentAudit\Exceptions\Exception;
use Overtrue\LaravelQcloudContentAudit\Exceptions\InvalidImageException;
use Overtrue\LaravelQcloudContentAudit\Exceptions\InvalidTextException;
use Overtrue\LaravelQcloudContentAudit\Traits\HasStrategies;
use TencentCloud\Ims\V20201229\Models\ImageModerationRequest;

class Ims
{
    use HasStrategies;

    public const MAX_SIZE = 300;

    public const DEFAULT_STRATEGY = 'strict';

    protected ?string $bizType = null;

    /**
     * @throws Exception
     */
    public function check(string $contents)
    {
        $key = 'FileContent';

        if (\filter_var($contents, \FILTER_VALIDATE_URL)) {
            $key = 'FileUrl';
        }

        if ($key === 'FileContent') {
            $contents = $this->resizeImage($contents);
        }

        $request = new ImageModerationRequest;
        $request->fromJsonString(\json_encode(array_filter([
            $key => \base64_encode($contents),
            'BizType' => $this->bizType,
        ])));

        $response = \json_decode(
            \app('ims-service')
                ->ImageModeration($request)
                ->toJsonString(),
            true
        );

        if (empty($response['Suggestion'])) {
            throw new Exception('API 调用失败(empty response)');
        }

        return $response;
    }

    /**
     * @throws InvalidTextException
     * @throws Exception
     */
    public function validate(string $contents, string $strategy = self::DEFAULT_STRATEGY): bool
    {
        if (\Overtrue\LaravelQcloudContentAudit\Ims::dry()) {
            return true;
        }

        $response = $this->check($contents);

        if (! $this->satisfiesStrategy($response, $strategy)) {
            throw new InvalidImageException($response);
        }

        return true;
    }

    protected function resizeImage(string $contents): string
    {
        $driver = config('image.driver', config('images.default', 'gd'));
        $manager = ImageManager::usingDriver(match ($driver) {
            'gd' => GdDriver::class,
            'imagick' => ImagickDriver::class,
            default => $driver,
        }, autoOrientation: false, decodeAnimation: false);

        return $manager->decode($contents)
            ->scale(self::MAX_SIZE, self::MAX_SIZE)
            ->encode(new AutoEncoder(quality: 90))
            ->toString();
    }

    public function setBizType(?string $bizType): self
    {
        $this->bizType = $bizType;

        return $this;
    }
}
