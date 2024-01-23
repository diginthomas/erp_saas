<?php

namespace Tests\Feature\Controller;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Modules\Core\Facades\MailableTemplates;
use Tests\TestCase;

class SystemToolsControllerTest extends TestCase
{
    public function test_unauthenticated_user_cannot_access_system_tools_endpoints()
    {
        $this->getJson('/api/tools/json-language')->assertUnauthorized();
        $this->getJson('/api/tools/storage-link')->assertUnauthorized();
        $this->getJson('/api/tools/clear-cache')->assertUnauthorized();
        $this->getJson('/api/tools/optimize')->assertUnauthorized();
        $this->getJson('/api/tools/seed-mailables')->assertUnauthorized();
    }

    public function test_unauthorized_user_cannot_access_system_tools_endpoints()
    {
        $this->asRegularUser()->signIn();

        $this->getJson('/api/tools/json-language')->assertForbidden();
        $this->getJson('/api/tools/storage-link')->assertForbidden();
        $this->getJson('/api/tools/clear-cache')->assertForbidden();
        $this->getJson('/api/tools/optimize')->assertForbidden();
        $this->getJson('/api/tools/seed-mailables')->assertForbidden();
    }

    public function test_i18_generate_tool_can_be_executed()
    {
        $this->signIn();

        $this->getJson('/api/tools/json-language')->assertOk();

        $this->assertLessThanOrEqual(2, Carbon::parse(filemtime(config('translator.json')))->diffInSeconds());
    }

    public function test_storage_link_tool_can_be_executed()
    {
        $this->signIn();

        Artisan::shouldReceive('call')
            ->once()
            ->with('storage:link', []);

        $this->getJson('/api/tools/storage-link');
    }

    public function test_clear_cache_tool_can_be_executed()
    {
        $this->signIn();

        Artisan::shouldReceive('call')
            ->once()
            ->with(config('core.commands.clear-cache', 'optimize:clear'), []);

        $this->getJson('/api/tools/clear-cache');
    }

    public function test_optimize_tool_can_be_executed()
    {
        $this->signIn();

        Artisan::shouldReceive('call')
            ->once()
            ->with(config('core.commands.optimize', 'optimize'), []);

        $this->getJson('/api/tools/optimize');
    }

    public function test_seed_mailable_templates_tool_can_be_executed()
    {
        $this->signIn();

        MailableTemplates::spy();

        $this->getJson('/api/tools/seed-mailables')->assertOk();

        MailableTemplates::shouldHaveReceived('seedIfRequired')->once();
    }
}
