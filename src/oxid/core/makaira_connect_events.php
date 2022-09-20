<?php
/**
 * This file is part of a marmalade GmbH project
 *
 * It is not Open Source and may not be redistributed.
 * For contact information please visit http://www.marmalade.de
 *
 * Version:    1.0
 * Author:     Alexander Kraus <kraus@marmalade.de>
 * Author URI: http://www.marmalade.de
 */

/**
 * The container class for OXIDs module events.
 * We use it to create tables and new columns on activation, and deleting our blocks on deactivation.
 */
class makaira_connect_events
{
    /**
     * Execute action on activate event
     */
    public static function onActivate()
    {
        //Don't show errors. This happens by default in ADODbLight
        error_reporting(0);

        // Support Oxid 5.1
        self::checkOxConfigOxVarNameLength();

        // Add new table to configurate landing pages
        self::addProductSequenceTable();
        self::addUserTokenTable();
        self::migrate();
        // Oxid CE/PE compatibility
        $isOxid6 = oxRegistry::get('makaira_connect_helper')->isOxid6();
        self::addColumnsToOxobject2category($isOxid6);

        // Oxid 6 compatibility
        self::addOxTagsToOxArtExtends();

        self::migrateTrackingSettings();

        $oDbHandler = oxNew("oxDbMetaDataHandler");
        $oDbHandler->updateViews();
    }

    /**
     * Execute action on deactivate event
     */
    public static function onDeactivate()
    {
        self::cleanupTplBlocks();
    }

    private static function migrateTrackingSettings()
    {
        $oxidConfig  = oxRegistry::getConfig();
        $oldSiteId = $oxidConfig->getShopConfVar(
            'makaira_tracking_page_id',
            null,
            oxConfig::OXMODULE_MODULE_PREFIX . 'makaira/tracking'
        );

        $newSiteId = $oxidConfig->getShopConfVar(
            'makaira_tracking_page_id',
            null,
            oxConfig::OXMODULE_MODULE_PREFIX . 'makaira/connect'
        );

        if ($oldSiteId && !$newSiteId) {
            $oxidConfig->saveShopConfVar(
                'str',
                'makaira_tracking_page_id',
                $oldSiteId,
                null,
                oxConfig::OXMODULE_MODULE_PREFIX . 'makaira/connect'
            );
        }
    }
    /**
     * Add new table to configurate landing pages
     */
    private static function addProductSequenceTable()
    {
        $sSql = "CREATE TABLE IF NOT EXISTS `makaira_connect_changes` (
            `SEQUENCE` BIGINT NOT NULL AUTO_INCREMENT,
            `TYPE` VARCHAR(32) COLLATE latin1_general_ci NOT NULL,
            `OXID` CHAR(32) COLLATE latin1_general_ci NOT NULL,
            `CHANGED` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX (`OXID`),
            PRIMARY KEY (`SEQUENCE`),
            UNIQUE KEY `uniqueChanges` (`TYPE`, `OXID`)
        ) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;";
        oxDb::getDb()->execute($sSql);
    }

    private static function addUserTokenTable()
    {
        $sSql = "CREATE TABLE IF NOT EXISTS `makaira_connect_usertoken` (
            `USERID` CHAR(32) COLLATE latin1_general_ci NOT NULL,
            `TOKEN` VARCHAR(255),
            `VALID_UNTIL` DATETIME,
            INDEX (`TOKEN`, `VALID_UNTIL`),
            UNIQUE (`TOKEN`),
            PRIMARY KEY (`USERID`)
        ) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;";
        oxDb::getDb()->execute($sSql);
    }

    /**
     * Add OXSHOPID to oxobject2category to ensure CE/PE compatibility
     */
    private static function addColumnsToOxobject2category($isOxid6 = false)
    {
        if (!self::hasColumn('oxobject2category', 'OXSHOPID')) {
            $sSql = "ALTER TABLE oxobject2category
                     ADD OXSHOPID VARCHAR(32) NOT NULL DEFAULT 'oxbaseshop'";
            if ($isOxid6) {
                $sSql = "ALTER TABLE oxobject2category ADD OXSHOPID INT(11) NOT NULL DEFAULT 1";
            }

            oxDb::getDb()->execute($sSql);
        }
    }

    /**
     * Add OXTAGS to oxartextends to ensure OXID6 compatibility
     */
    private static function addOxTagsToOxArtExtends()
    {
        if (!self::hasColumn('oxartextends', 'OXTAGS')) {
            $sSql = "ALTER TABLE oxartextends
                     ADD OXTAGS VARCHAR(255) NOT NULL COMMENT 'Tags (multilanguage)',
                     ADD OXTAGS_1 varchar(255) NOT NULL,
                     ADD OXTAGS_2 varchar(255) NOT NULL,
                     ADD OXTAGS_3 varchar(255) NOT NULL";
            oxDb::getDb()->execute($sSql);
        }
    }

    private static function isMigrationRequired()
    {
        $dbName = oxRegistry::getConfig()->getConfigParam('dbName');
        $keyColumnCount = (int) oxDb::getDb(true)->getOne(
            "SELECT COUNT(*)
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
            WHERE
                CONSTRAINT_SCHEMA = '{$dbName}' AND
                CONSTRAINT_NAME = 'uniqueChanges' AND
                TABLE_NAME = 'makaira_connect_changes'"
        );

        return 0 == $keyColumnCount;
    }

    private static function migrate()
    {
        if (self::isMigrationRequired()) {
            $db = oxDb::getDb(true);

            // Create the migration table
            $db->execute('CREATE TABLE makaira_connect_changes_migrate LIKE makaira_connect_changes');

            // Add unique key constraint
            $db->execute('ALTER TABLE makaira_connect_changes_migrate ADD UNIQUE KEY `uniqueChanges` (`TYPE`, `OXID`)');

            // Copy unique rows
            $db->execute('INSERT INTO makaira_connect_changes_migrate (SEQUENCE, TYPE, OXID, CHANGED)
                SELECT MAX(SEQUENCE), TYPE, OXID, MAX(CHANGED) FROM makaira_connect_changes GROUP BY TYPE, OXID;');

            // Remove old table
            $db->execute('DROP TABLE makaira_connect_changes');

            // Rename migration table
            $db->execute('ALTER TABLE makaira_connect_changes_migrate RENAME TO makaira_connect_changes');
        }
    }

    private static function cleanupTplBlocks()
    {
        $shopId = oxRegistry::getConfig()->getShopId();

        $db = oxDb::getDb();
        $db->execute("DELETE FROM `oxtplblocks` WHERE `OXMODULE` = 'makaira/connect' AND `OXSHOPID` = '{$shopId}'");
    }

    /**
     * Checks if $column exists in $table
     *
     * @param string $table
     * @param string $column
     *
     * @return boolean true if $column exists in $table, else false
     */
    private static function hasColumn($table, $column)
    {
        $dbName = oxRegistry::getConfig()->getConfigParam('dbName');
        $keyColumnCount = (int) oxDb::getDb(true)->getOne(
            "SELECT COUNT(*)
             FROM information_schema.COLUMNS 
             WHERE 
                 TABLE_SCHEMA = '{$dbName}' 
             AND TABLE_NAME = '{$table}'
             AND COLUMN_NAME = '{$column}'"
        );

        return 0 < $keyColumnCount;
    }


    private static function checkOxConfigOxVarNameLength()
    {
        $databaseName = oxRegistry::getConfig()->getConfigParam('dbName');

        // Check field OXVARNAME in table oxconfig != VARCHAR(100)
        $oxDb  = oxDb::getDb();
        $fieldLength = $oxDb->getOne(
            "SELECT CHARACTER_MAXIMUM_LENGTH 
            FROM information_schema.COLUMNS 
            WHERE 
                TABLE_SCHEMA=? 
                AND TABLE_NAME='oxconfig' 
                AND COLUMN_NAME='OXVARNAME'",
            [$databaseName]
        );

        // Extend field length
        if ((false !== $fieldLength) && ((int) $fieldLength < 50)) {
            $oxDb->execute(
                "ALTER TABLE oxconfig MODIFY COLUMN OXVARNAME VARCHAR(100) NOT NULL DEFAULT '' COMMENT 'Variable name'"
            );
        }
    }
}
