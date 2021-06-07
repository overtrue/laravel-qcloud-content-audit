<?php

namespace Overtrue\LaravelQcs\Moderators;

use Intervention\Image\Facades\Image;
use Overtrue\LaravelQcs\Exceptions\Exception;
use Overtrue\LaravelQcs\Exceptions\InvalidTextException;
use Overtrue\LaravelQcs\Traits\HasStrategies;
use TencentCloud\Ims\V20201229\Models\ImageModerationRequest;

class Ims
{
    use HasStrategies;

    public const MAX_SIZE = 300;

    public const DEFAULT_STRATEGY = 'strict';

    /**
     * @throws \Overtrue\LaravelQcs\Exceptions\Exception
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
        $request->fromJsonString(\json_encode([$key => \base64_encode($contents)]));

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
     * @throws \Overtrue\LaravelQcs\Exceptions\InvalidTextException
     * @throws \Overtrue\LaravelQcs\Exceptions\Exception
     */
    public function validate(string $contents, string $strategy = self::DEFAULT_STRATEGY): bool
    {
        if (!$this->satisfiesStrategy($this->check($contents), $strategy)) {
            throw new InvalidTextException('Invalid image contents.');
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
}
