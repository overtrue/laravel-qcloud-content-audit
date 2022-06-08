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
                $httpProfile = new HttpProfile();
                $httpProfile->setEndpoint(config('services.tms.endpoint', 'tms.tencentcloudapi.com'));

                $clientProfile = new ClientProfile();
                $clientProfile->setHttpProfile($httpProfile);

                return new TmsClient($credential, config('services.tms.region', 'ap-guangzhou'), $clientProfile);
            }
        );

        $this->app->singleton(
            'tms',
            function () {
                return \tap(
                    new \Overtrue\LaravelQcloudContentAudit\Moderators\Tms(),
                    function (\Overtrue\LaravelQcloudContentAudit\Moderators\Tms $tms) {
                        $tms->setStrategy('strict', fn ($result) => $result['Suggestion'] === 'Pass');
                    }
                );
            }
        );

        $this->app->singleton(
            'ims-service',
            function () {
                $credential = new Credential(config('services.ims.secret_id'), config('services.ims.secret_key'));
                $httpProfile = new HttpProfile();
                $httpProfile->setEndpoint(config('services.ims.endpoint', 'ims.tencentcloudapi.com'));

                $clientProfile = new ClientProfile();
                $clientProfile->setHttpProfile($httpProfile);

                return new ImsClient($credential, config('services.ims.region', 'ap-guangzhou'), $clientProfile);
            }
        );

        $this->app->singleton(
            'ims',
            function () {
                return \tap(
                    new \Overtrue\LaravelQcloudContentAudit\Moderators\Ims(),
                    function (\Overtrue\LaravelQcloudContentAudit\Moderators\Ims $ims) {
                        $ims->setStrategy('strict', fn ($result) => $result['Suggestion'] === 'Pass');
                    }
                );
            }
        );
    }

    public function boot()
    {
        app('validator')->extend('tms', function ($attribute, $value, $parameters, $validator) {
            return (new \Overtrue\LaravelQcloudContentAudit\Rules\Tms($parameters[0] ?? \Overtrue\LaravelQcloudContentAudit\Moderators\Tms::DEFAULT_STRATEGY))->passes($attribute, $value);
        });

        app('validator')->extend('ims', function ($attribute, $value, $parameters, $validator) {
            return (new \Overtrue\LaravelQcloudContentAudit\Rules\Ims($parameters[0] ?? \Overtrue\LaravelQcloudContentAudit\Moderators\Ims::DEFAULT_STRATEGY))->passes($attribute, $value);
        });
    }

    public function provides(): array
    {
        return ['tms', 'ims', 'tms-service', 'ims-service'];
    }
}
