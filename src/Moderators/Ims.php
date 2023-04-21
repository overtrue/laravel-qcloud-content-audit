<?php

namespace Overtrue\LaravelQcloudContentAudit\Moderators;

use Intervention\Image\Facades\Image;
use Overtrue\LaravelQcloudContentAudit\Exceptions\Exception;
use Overtrue\LaravelQcloudContentAudit\Exceptions\InvalidImageException;
use Overtrue\LaravelQcloudContentAudit\Traits\HasStrategies;
use TencentCloud\Ims\V20201229\Models\ImageModerationRequest;

class Ims
{
    use HasStrategies;

    public const MAX_SIZE = 300;

    public const DEFAULT_STRATEGY = 'strict';

    protected ?string $bizType = null;

    /**
     * @throws \Overtrue\LaravelQcloudContentAudit\Exceptions\Exception
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

        $request = new ImageModerationRequest();
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
     * @throws \Overtrue\LaravelQcloudContentAudit\Exceptions\InvalidTextException
     * @throws \Overtrue\LaravelQcloudContentAudit\Exceptions\Exception
     */
    public function validate(string $contents, string $strategy = self::DEFAULT_STRATEGY): bool
    {
        if (config('services.ims.disable', false)) {
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
        $img = Image::make($contents);

        $img->resize(
            self::MAX_SIZE,
            self::MAX_SIZE,
            function ($constraint) {
                $constraint->aspectRatio();
            }
        );

        return $img->stream()->getContents();
    }

    public function setBizType(?string $bizType): self
    {
        $this->bizType = $bizType;

        return $this;
    }
}
