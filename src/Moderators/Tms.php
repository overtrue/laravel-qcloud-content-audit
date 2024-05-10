<?php

namespace Overtrue\LaravelQcloudContentAudit\Moderators;

use Illuminate\Support\Facades\Log;
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
            'BizType' => $this->bizType,
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
        if (\Overtrue\LaravelQcloudContentAudit\Tms::dry()) {
            return true;
        }

        $response = $this->check($contents);

        if (! $this->satisfiesStrategy($response, $strategy)) {
            throw new InvalidTextException('Invalid text', $contents, $response);
        }

        return true;
    }

    /**
     * @throws \Overtrue\LaravelQcloudContentAudit\Exceptions\Exception
     */
    public function mask(string|array $input, string $strategy = self::DEFAULT_STRATEGY, string $char = '*'): string|array
    {
        $inputIsArray = is_array($input);

        $contents = $inputIsArray ? json_encode($input, JSON_UNESCAPED_UNICODE) : $input;

        if (mb_strlen(preg_replace('/[\s]+/', '', $contents)) < 1) {
            return $input;
        }

        // 接口有长度限制，所以需要分片处理
        $slices = mb_str_split($contents, 3000);

        $result = '';

        foreach ($slices as $slice) {
            $result .= $this->maskString($slice, $strategy, $char);
        }

        if ($inputIsArray) {
            // 数组结构时，需要还原并替换到原数组
            $result = json_decode($result, true);

            if (! is_array($result) || count($result) !== count($input)) {
                Log::error('TMS: Masked array length mismatch.', [
                    'input' => $input,
                    'result' => $result,
                ]);

                return $input;
            }

            $result = array_combine(array_keys($input), $result);
        }

        return $result;
    }

    /**
     * @throws \Overtrue\LaravelQcloudContentAudit\Exceptions\Exception
     */
    protected function maskString(string $contents, string $strategy = self::DEFAULT_STRATEGY, string $char = '*'): string
    {
        if (\Overtrue\LaravelQcloudContentAudit\Tms::dry()) {
            return $contents;
        }

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
