<?php
namespace Bim\Db\Entity;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;

use Bim\Util\Config;

/**
 * Class MigrationsTable
 *
 * Documentation: http://cjp2600.github.io/bim-core/
 * @package Bim\Db\Entity
 */
class MigrationsTable
{
    /**
     * getTableName
     * @return string
     */
    public static function getTableName()
    {
        $conf = new Config();
        $tableName = $conf->get("migration_table");
        if (MODULE_MIGRATIONS) {
            $tableName .= '_'.str_replace('.', '_', MODULE_NAME);
        }
        return $tableName;
    }

    /**
     * isExistsInTable
     * @param $id
     * @return bool
     * @throws \Exception
     */
    public static function isExistsInTable($id)
    {
        # check migration tables
        self::checkMigrationTable();

        global $DB;
        if ($result = $DB->Query("SELECT 'id' FROM " . self::getTableName() . " WHERE id = '" . $id . "'", true)) {
            if ($result->AffectedRowsCount()) {
                return true;
            }
        } else {
            throw new \Exception($DB->GetErrorMessage());
        }
        return false;
    }

    /**
     * add
     * @param $id
     * @return bool
     * @throws Exception
     */
    public static function add($id)
    {
        global $DB;
        if (!self::isExistsInTable($id)) {
            $DB->Add(self::getTableName(), array(
                "id" => $id
            ));
            if (self::isExistsInTable($id)) {
                return true;
            }
        }
        return false;
    }

    /**
     * delete
     * @param $id
     * @return bool
     * @throws \Exception
     */
    public static function delete($id)
    {
        global $DB;
        if ($result = $DB->Query("DELETE FROM " . self::getTableName() . " WHERE id = '" . $id . "'", true)) {
            return true;
        } else {
            throw new \Exception($DB->GetErrorMessage());
        }
        return false;
    }


    /**
     * checkMigrationTable
     * @throws Exception
     */
    public static function checkMigrationTable()
    {
        global $DB;
        if (!$DB->Query("SELECT 'id' FROM " . self::getTableName(), true)) {
            throw new \Exception("Migration table not found, run init command. Example: php bim init");
        }
    }

    /**
     * createTable
     * @return bool
     * @throws \Exception
     */
    public static function createTable()
    {
        global $DB;
        $errors = false;
        if (!$DB->Query("SELECT 'id' FROM " . self::getTableName(), true)) {
            $DB->Query('CREATE TABLE IF NOT EXISTS `'.self::getTableName().'` (
  `id` VARCHAR(255)
       COLLATE utf8_unicode_ci NOT NULL
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci', false);
            $errors = $DB->GetErrorMessage();
        } else {
            return false;
        }
        if (!empty($errors)) {
            throw new \Exception($errors);
            return false;
        }
        return true;
    }

}
