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

namespace Modules\Core\Tests\Feature;

use Akaunting\Money\Currency;
use Tests\TestCase;

class CurrencyControllerTest extends TestCase
{
    public function test_unauthenticated_cannot_access_currency_endpoints()
    {
        $this->getJson('/api/currencies')->assertUnauthorized();
    }

    public function test_user_can_fetch_currencies()
    {
        $this->signIn();

        $this->getJson('/api/currencies')
            ->assertOk()
            ->assertJson(Currency::getCurrencies());
    }
}
