<?php

declare(strict_types=1);

/*
 * This file is part of the IncludeInfoBundle.
 *
 * (c) INSPIRED MINDS
 *
 * @license LGPL-3.0-or-later
 */

namespace InspiredMinds\IncludeInfoBundle\Migration;

use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Column;

/**
 * Truncates the tl_inserttag_index if columns are greater than the requested size.
 */
class IndexColumnLengthMigration extends AbstractMigration
{
    private const TABLE = 'tl_inserttag_index';

    private static array $columns = ['tag', 'params', 'flags'];

    public function __construct(
        private readonly Connection $db,
        private readonly ContaoFramework $framework,
        private readonly bool $enableIndex,
    ) {
    }

    public function shouldRun(): bool
    {
        if (!$this->enableIndex) {
            return false;
        }

        $schemaManager = $this->db->createSchemaManager();

        if (!$schemaManager->tablesExist([self::TABLE])) {
            return false;
        }

        // Check if there are entries
        if (!$this->db->fetchOne(\sprintf('SELECT TRUE FROM %s', $this->db->quoteIdentifier(self::TABLE)))) {
            return false;
        }

        $columns = $schemaManager->listTableColumns(self::TABLE);
        $this->framework->initialize();
        Controller::loadDataContainer(self::TABLE);

        // Check if any of the columns needs to be migrated to the lower column length
        foreach ($columns as $column) {
            if ($this->needsMigration($column)) {
                return true;
            }
        }

        return false;
    }

    public function run(): MigrationResult
    {
        // Truncate the entries so that there are no errors while reducing the column length
        $this->db->executeStatement(\sprintf('TRUNCATE %s', $this->db->quoteIdentifier(self::TABLE)));

        return $this->createResult(true);
    }

    private function needsMigration(Column $column): bool
    {
        if (!\in_array($column->getName(), self::$columns, true)) {
            return false;
        }

        $requestedLength = $GLOBALS['TL_DCA'][self::TABLE]['fields'][$column->getName()]['sql']['length'] ?? null;

        if (null === $requestedLength) {
            return false;
        }

        return $column->getLength() > $requestedLength;
    }
}
