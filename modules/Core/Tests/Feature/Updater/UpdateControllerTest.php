<?php
/**
 * Concord CRM - https://www.concordcrm.com
 *
 * @version   1.3.5
 *
 * @link      Releases - https://www.concordcrm.com/releases
 * @link      Terms Of Service - https://www.concordcrm.com/terms
 *
 * @copyright Copyright (c) 2022-2024 KONKORD DIGITAL
 */

namespace Modules\Core\Tests\Feature\Updater;

use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;
use Modules\Core\Updater\Updater;
use Tests\TestCase;

/**
 * @group updater
 */
class UpdateControllerTest extends TestCase
{
    use TestsUpdater;

    public function test_unauthenticated_user_cannot_access_update_endpoints()
    {
        $this->getJson('api/update')->assertUnauthorized();
        $this->getJson('api/update/FAKE_KEY')->assertUnauthorized();
    }

    public function test_unauthorized_user_cannot_access_update_endpoints()
    {
        $this->asRegularUser()->signIn();

        $this->getJson('api/update')->assertForbidden();
        $this->postJson('api/update/FAKE_KEY')->assertForbidden();
    }

    public function test_it_forces_update_when_user_initiate_update_via_ui()
    {
        $this->signIn();

        Artisan::shouldReceive('call')
            ->once()
            ->with('updater:update', ['--key' => null, '--force' => true]);

        $this->postJson('api/update');
    }

    public function test_update_information_can_be_retrieved()
    {
        $this->signIn();

        App::singleton(Updater::class, function () {
            return $this->createUpdaterInstance([
                new Response(200, [], $this->archiveResponse()),
            ], ['version_installed' => '1.1.0']);
        });

        $this->getJson('/api/update')->assertExactJson([
            'installed_version' => '1.1.0',
            'is_new_version_available' => false,
            'latest_available_version' => '1.1.0',
            'purchase_key' => config('updater.purchase_key'),
        ]);
    }
}
