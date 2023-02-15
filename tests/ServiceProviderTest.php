<?php

namespace Tests;

use Illuminate\Contracts\Support\DeferrableProvider;
use Overtrue\LaravelQcloudContentAudit\Ims;
use Overtrue\LaravelQcloudContentAudit\Moderators\Ims as ImsModerator;
use Overtrue\LaravelQcloudContentAudit\Moderators\Tms as TmsModerator;
use Overtrue\LaravelQcloudContentAudit\QcloudContentAuditServiceProvider;
use Overtrue\LaravelQcloudContentAudit\Tms;
use TencentCloud\Ims\V20201229\ImsClient;
use TencentCloud\Tms\V20201229\TmsClient;

class ServiceProviderTest extends TestCase
{
    public function test_services_are_registered()
    {
        $this->assertInstanceOf(DeferrableProvider::class, new QcloudContentAuditServiceProvider($this->app));

        $this->assertInstanceOf(TmsClient::class, app('tms-service'));
        $this->assertInstanceOf(ImsClient::class, app('ims-service'));

        $this->assertInstanceOf(TmsModerator::class, app('tms'));
        $this->assertInstanceOf(ImsModerator::class, app('ims'));

        $this->assertInstanceOf(TmsModerator::class, Tms::getFacadeRoot());
        $this->assertInstanceOf(ImsModerator::class, Ims::getFacadeRoot());
    }
}
