<?php

namespace Tests;

use Illuminate\Contracts\Support\DeferrableProvider;
use Overtrue\LaravelQcs\Ims;
use Overtrue\LaravelQcs\Moderators\Tms as TmsModerator;
use Overtrue\LaravelQcs\Moderators\Ims as ImsModerator;
use Overtrue\LaravelQcs\QcsServiceProvider;
use Overtrue\LaravelQcs\Tms;
use TencentCloud\Ims\V20201229\ImsClient;
use TencentCloud\Tms\V20201229\TmsClient;

class ServiceProviderTest extends TestCase
{
    public function test_services_are_registered()
    {
        $this->assertInstanceOf(DeferrableProvider::class, new QcsServiceProvider($this->app));

        $this->assertInstanceOf(TmsClient::class, app('tms-service'));
        $this->assertInstanceOf(ImsClient::class, app('ims-service'));

        $this->assertInstanceOf(TmsModerator::class, app('tms'));
        $this->assertInstanceOf(ImsModerator::class, app('ims'));

        $this->assertInstanceOf(TmsModerator::class, Tms::getFacadeRoot());
        $this->assertInstanceOf(ImsModerator::class, Ims::getFacadeRoot());
    }
}
