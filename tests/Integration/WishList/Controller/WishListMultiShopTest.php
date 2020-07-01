<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Account\Tests\Integration\WishList\Controller;

use OxidEsales\Eshop\Core\Registry as EshopRegistry;
use OxidEsales\GraphQL\Base\Tests\Integration\MultishopTestCase;

final class WishListMultiShopTest extends MultishopTestCase
{
    private const USERNAME = 'user@oxid-esales.com';

    private const PASSWORD = 'useruser';

    private const LIST_OWNER_ID_SHOP_1 = 'e7af1c3b786fd02906ccd75698f4e6b9'; // customer shop 1 -> user@oxid-esales.com

    public function testGetWishListFromDifferentShop(): void
    {
        $this->setGETRequestParameter('shp', '2');

        $this->prepareToken(self::USERNAME, self::PASSWORD);

        $result = $this->wishListByOwnerQuery(self::LIST_OWNER_ID_SHOP_1);
        $this->assertResponseStatus(404, $result);
    }

    public function testGetWishListForMallUser(): void
    {
        EshopRegistry::getConfig()->setConfigParam('blMallUsers', true);
        $this->setGETRequestParameter('shp', '2');

        $this->prepareToken(self::USERNAME, self::PASSWORD);

        $result = $this->wishListByOwnerQuery(self::LIST_OWNER_ID_SHOP_1);
        $this->assertResponseStatus(200, $result);
    }

    private function wishListByOwnerQuery(string $ownerId): array
    {
        return $this->query('query {
            wishListByOwnerId(ownerId: "' . $ownerId . '") {
                id
                public
            }
        }');
    }
}
