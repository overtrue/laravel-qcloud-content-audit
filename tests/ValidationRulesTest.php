<?php

namespace Tests;

use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\ProviderRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Validator;
use Illuminate\Translation\ArrayLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\ValidationServiceProvider;
use Overtrue\LaravelQcloudContentAudit\Ims;
use Overtrue\LaravelQcloudContentAudit\Moderators\Ims as ImsModerator;
use Overtrue\LaravelQcloudContentAudit\Moderators\Tms as TmsModerator;
use Overtrue\LaravelQcloudContentAudit\QcloudContentAuditServiceProvider;
use Overtrue\LaravelQcloudContentAudit\Tms;
use PHPUnit\Framework\TestCase;

class ValidationRulesTest extends TestCase
{
    protected Application $app;

    protected string $manifestPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app = new Application;
        $this->app->instance('config', new Repository);
        $this->app->instance('translator', new Translator(new ArrayLoader, 'en'));
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication($this->app);

        $this->manifestPath = tempnam(sys_get_temp_dir(), 'qcloud-providers-');
        unlink($this->manifestPath);

        // Use normal provider discovery without Testbench's console bootstrap,
        // which eagerly loads every deferred provider and hides first-use bugs.
        (new ProviderRepository($this->app, new Filesystem, $this->manifestPath))->load([
            ValidationServiceProvider::class,
            QcloudContentAuditServiceProvider::class,
        ]);

        $this->app->boot();
    }

    protected function tearDown(): void
    {
        try {
            \Mockery::close();
        } finally {
            Facade::clearResolvedInstances();
            Facade::setFacadeApplication(null);
            $this->app->flush();
            Container::setInstance(null);
            unlink($this->manifestPath);
            parent::tearDown();
        }
    }

    public function test_text_rule_is_registered_before_resolving_package_services()
    {
        $this->assertFalse($this->app->resolved(TmsModerator::class));
        $this->assertFalse($this->app->resolved('tms-service'));

        // Build the validator before mocking the facade, which resolves the service.
        $validator = Validator::make(['name' => 'Safe content'], ['name' => 'required|tms']);

        Tms::shouldReceive('validate')
            ->once()
            ->with('Safe content', TmsModerator::DEFAULT_STRATEGY)
            ->andReturnTrue();

        $this->assertTrue($validator->passes());
    }

    public function test_image_rule_is_registered_before_resolving_package_services()
    {
        $this->assertFalse($this->app->resolved(ImsModerator::class));
        $this->assertFalse($this->app->resolved('ims-service'));

        $contents = file_get_contents(__DIR__.'/images/500x500.png');
        $image = UploadedFile::fake()->createWithContent('logo.png', $contents);

        // Build the validator before mocking the facade, which resolves the service.
        $validator = Validator::make(['logo' => $image], ['logo' => 'required|ims']);

        Ims::shouldReceive('validate')
            ->once()
            ->with($contents, ImsModerator::DEFAULT_STRATEGY)
            ->andReturnTrue();

        $this->assertTrue($validator->passes());
    }
}
