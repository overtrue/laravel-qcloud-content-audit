<?php

namespace Overtrue\LaravelQcloudContentAudit;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use TencentCloud\Common\Credential;
use TencentCloud\Common\Profile\ClientProfile;
use TencentCloud\Common\Profile\HttpProfile;
use TencentCloud\Ims\V20201229\ImsClient;
use TencentCloud\Tms\V20201229\TmsClient;

class QcloudContentAuditServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function register()
    {
        $this->app->singleton(
            'tms-service',
            function () {
                $credential = new Credential(config('services.tms.secret_id'), config('services.tms.secret_key'));
                $httpProfile = new HttpProfile;
                $httpProfile->setEndpoint(config('services.tms.endpoint', 'tms.tencentcloudapi.com'));

                $clientProfile = new ClientProfile;
                $clientProfile->setHttpProfile($httpProfile);

                return new TmsClient($credential, config('services.tms.region', 'ap-guangzhou'), $clientProfile);
            }
        );

        $this->app->singleton(
            Moderators\Tms::class,
            function () {
                return \tap(
                    new Moderators\Tms,
                    function (Moderators\Tms $tms) {
                        $tms->setStrategy('strict', fn ($result) => $result['Suggestion'] === 'Pass');
                        $tms->setBizType(config('services.tms.biz_type'));

                        Tms::dry(config('services.tms.dry', false));
                    }
                );
            }
        );

        $this->app->singleton(
            'ims-service',
            function () {
                $credential = new Credential(config('services.ims.secret_id'), config('services.ims.secret_key'));
                $httpProfile = new HttpProfile;
                $httpProfile->setEndpoint(config('services.ims.endpoint', 'ims.tencentcloudapi.com'));

                $clientProfile = new ClientProfile;
                $clientProfile->setHttpProfile($httpProfile);

                return new ImsClient($credential, config('services.ims.region', 'ap-guangzhou'), $clientProfile);
            }
        );

        $this->app->singleton(
            Moderators\Ims::class,
            function () {
                return \tap(
                    new Moderators\Ims,
                    function (Moderators\Ims $ims) {
                        $ims->setStrategy('strict', fn ($result) => $result['Suggestion'] === 'Pass');
                        $ims->setBizType(config('services.ims.biz_type'));

                        Ims::dry(config('services.ims.dry', false));
                    }
                );
            }
        );

        $this->app->alias(Moderators\Tms::class, 'tms');
        $this->app->alias(Moderators\Ims::class, 'ims');
    }

    public function boot()
    {
        app('validator')->extend('tms', function ($attribute, $value, $parameters, $validator) {
            return (new Rules\Tms($parameters[0] ?? Moderators\Tms::DEFAULT_STRATEGY))->passes($attribute, $value);
        });

        app('validator')->extend('ims', function ($attribute, $value, $parameters, $validator) {
            return (new Rules\Ims($parameters[0] ?? Moderators\Ims::DEFAULT_STRATEGY))->passes($attribute, $value);
        });
    }

    public function provides(): array
    {
        return [
            'tms-service', 'ims-service',
            Moderators\Tms::class,
            Moderators\Ims::class,
            'tms', 'ims',
        ];
    }
}
