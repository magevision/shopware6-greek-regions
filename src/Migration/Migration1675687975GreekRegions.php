<?php declare(strict_types=1);

namespace MageVisionGreekRegions\Migration;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Migration\MigrationStep;
use Shopware\Core\Framework\Uuid\Uuid;
use Doctrine\DBAL\Driver\Exception as DriverException;

class Migration1675687975GreekRegions extends MigrationStep
{
    private const COUNTRY_CODE = 'GR';
    private const COUNTRY_LOCALE = 'Greek';
    private const COUNTRY_LOCALE_CODE = 'el-GR';

    /**
     * @return int
     */
    public function getCreationTimestamp(): int
    {
        return 1675687975;
    }

    /**
     * @param Connection $connection
     * @return void
     * @throws Exception
     * @throws DriverException
     */
    public function update(Connection $connection): void
    {
        $grId = $connection->executeQuery('SELECT id FROM country WHERE iso = "' . self::COUNTRY_CODE . '"')->fetchOne();
        if (!$grId) {
            return;
        }

        $greekLanguageId = $connection->executeQuery('SELECT id FROM language WHERE name = "' . self::COUNTRY_LOCALE . '"')->fetchOne();

        if (!$greekLanguageId) {
            $this->createGreekLanguage($connection);
            $greekLanguageId = $connection->executeQuery('SELECT id FROM language WHERE name = "' . self::COUNTRY_LOCALE . '"')->fetchOne();
        }

        $this->createCountryStates($connection, $grId, $greekLanguageId);
    }

    /**
     * @param Connection $connection
     * @return void
     */
    public function updateDestructive(Connection $connection): void
    {
    }

    /**
     * @param Connection $connection
     * @param string $countryId
     * @param string $greekLanguageId
     * @return void
     * @throws Exception
     */
    private function createCountryStates(Connection $connection, string $countryId, string $greekLanguageId): void
    {
        $data = [
            'GR-A' => 'Anatolikí Makedonía kai Thráki',
            'GR-I' => 'Attikí',
            'GR-G' => 'Dytikí Elláda',
            'GR-C' => 'Dytikí Makedonía',
            'GR-F' => 'Ionía Nísia',
            'GR-D' => 'Ípeiros',
            'GR-B' => 'Kentrikí Makedonía',
            'GR-M' => 'Kríti',
            'GR-L' => 'Nótio Aigaío',
            'GR-J' => 'Pelopónnisos',
            'GR-H' => 'Stereá Elláda',
            'GR-E' => 'Thessalía',
            'GR-K' => 'Vóreio Aigaío',
            'GR-69' => 'Ágion Óros',
        ];

        $greekTranslations = [
            'GR-A' => 'Ανατολική Μακεδονία και Θράκη',
            'GR-I' => 'Αττική',
            'GR-G' => 'Δυτική Ελλάδα',
            'GR-C' => 'Δυτική Μακεδονία',
            'GR-F' => 'Ιόνια Νησιά',
            'GR-D' => 'Ήπειρος',
            'GR-B' => 'Κεντρική Μακεδονία',
            'GR-M' => 'Κρήτη',
            'GR-L' => 'Νότιο Αιγαίο',
            'GR-J' => 'Πελοπόννησος',
            'GR-H' => 'Στερεά Ελλάδα',
            'GR-E' => 'Θεσσαλία',
            'GR-K' => 'Βόρειο Αιγαίο',
            'GR-69' => 'Άγιον Όρος',
        ];
        foreach ($data as $isoCode => $name) {
            $storageDate = (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT);
            $id = Uuid::randomBytes();
            $countryStateData = [
                'id' => $id,
                'country_id' => $countryId,
                'short_code' => $isoCode,
                'created_at' => $storageDate,
            ];
            $connection->insert('country_state', $countryStateData);
            $connection->insert('country_state_translation', [
                'language_id' => Uuid::fromHexToBytes(Defaults::LANGUAGE_SYSTEM),
                'country_state_id' => $id,
                'name' => $name,
                'created_at' => $storageDate,
            ]);

            if (isset($greekTranslations[$isoCode])) {
                $connection->insert('country_state_translation', [
                    'language_id' => $greekLanguageId,
                    'country_state_id' => $id,
                    'name' => $greekTranslations[$isoCode],
                    'created_at' => $storageDate,
                ]);
            }
        }
    }

    /**
     * @param Connection $connection
     * @return void
     * @throws Exception
     * @throws DriverException
     */
    private function createGreekLanguage(Connection $connection): void
    {
        $localeGr = $connection->executeQuery('SELECT id FROM locale WHERE code = "' . self::COUNTRY_LOCALE_CODE . '"')->fetchOne();
        $connection->insert('language', [
            'id' => Uuid::fromHexToBytes(Uuid::randomHex()),
            'name' => self::COUNTRY_LOCALE,
            'locale_id' => $localeGr,
            'translation_code_id' => $localeGr,
            'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);
    }
}
