<?php declare(strict_types=1);

namespace MageVision\GreekRegions;

use Doctrine\DBAL\Connection;
use MageVision\GreekRegions\Migration\Migration1675687975GreekRegions;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;

class MageVisionGreekRegions extends Plugin
{
    /**
     * @param UninstallContext $context
     * @return void
     */
    public function uninstall(UninstallContext $context): void
    {
        parent::uninstall($context);

        if ($context->keepUserData()) {
            return;
        }

        $connection = $this->container->get(Connection::class);
        $grId = $connection->executeQuery('SELECT id FROM country WHERE iso = "' . Migration1675687975GreekRegions::COUNTRY_CODE . '"')->fetchOne();
        $connection->executeStatement('DELETE FROM country_state WHERE country_id = :id', ['id' => $grId]);
    }
}
