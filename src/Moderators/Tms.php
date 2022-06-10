<?php

namespace Overtrue\LaravelQcloudContentAudit\Moderators;

use Overtrue\LaravelQcloudContentAudit\Exceptions\Exception;
use Overtrue\LaravelQcloudContentAudit\Exceptions\InvalidTextException;
use Overtrue\LaravelQcloudContentAudit\Traits\HasStrategies;
use TencentCloud\Tms\V20201229\Models\TextModerationRequest;

class Tms
{
    use HasStrategies;

    public const DEFAULT_STRATEGY = 'strict';

    protected ?string $bizType = null;

    /**
     * @throws \Overtrue\LaravelQcloudContentAudit\Exceptions\Exception
     */
    public function check(string $contents)
    {
        $request = new TextModerationRequest();
        $request->fromJsonString(\json_encode(array_filter([
            'Content' => \base64_encode($contents),
            'BizType' => $this->bizType
        ])));

        $response = \json_decode(
            \app('tms-service')
                ->TextModeration($request)
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
        $response = $this->check($contents);

        if (!$this->satisfiesStrategy($response, $strategy)) {
            throw new InvalidTextException($contents, $response);
        }

        return true;
    }

    /**
     * @throws \Overtrue\LaravelQcloudContentAudit\Exceptions\Exception
     */
    public function mask(string $contents, string $strategy = self::DEFAULT_STRATEGY, string $char = '*')
    {
        $result = $this->check($contents);

        $keywords = $result['Keywords'];

        if (empty($keywords) || $this->satisfiesStrategy($result, $strategy)) {
            return $contents;
        }

        $replaces = [];

        foreach ($keywords as $keyword) {
            $replaces[] = str_pad('', mb_strlen($keyword), $char);
        }

        return \str_replace($keywords, $replaces, $contents);
    }

    public function setBizType(?string $bizType): self
    {
        $this->bizType = $bizType;

        return $this;
    }
}
