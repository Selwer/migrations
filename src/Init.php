<?php

namespace Selwer\Migration;

use Selwer\Migration\ConnectBD;

class Init
{
    private $dbConn = null;
    private $pathFolder = null;
    private $dbHost = null; 
    private $dbName = null;
    private $dbUser = null; 
    private $dbPass = null; 
    private $dbTableVersion = null;

    function __construct($pathFolder, $dbHost, $dbName, $dbUser, $dbPass, $dbTableVersion)
    {
        $this->pathFolder = $pathFolder;
        $this->dbHost = $dbHost; 
        $this->dbName = $dbName;
        $this->dbUser = $dbUser; 
        $this->dbPass = $dbPass; 
        $this->dbTableVersion = $dbTableVersion;

        $db = ConnectBD::getInstance($dbHost, $dbName, $dbUser, $dbPass);
        $this->dbConn = $db->getConnection();
	}
    
    private function getMigrationFiles() 
    {
        if ($this->getMigrateAllFiles()) {

            $query = $this->dbConn->query("SHOW TABLES FROM " . $this->dbName . " LIKE '" . $this->dbTableVersion . "'");
            $firstMigration = $query->fetchAll(\PDO::FETCH_COLUMN);

            if (empty($firstMigration)) {
                $this->createTableVersions();
                return $allFiles;
            }
        
            $versionsFiles = [];
            $stmt = $this->dbConn->query("SELECT name FROM " .  $this->dbTableVersion);
            $stmt->execute([$this->dbTableVersion]);
            foreach ($stmt as $row) {
                array_push($versionsFiles, $sqlFolder . $row['name']);
            }

            return array_diff($allFiles, $versionsFiles);
        }

        return false;
    }

    private function getMigrateAllFiles()
    {
        if (file_exists($this->pathFolder)) {
            $sqlFolder = $this->pathFolder . DIRECTORY_SEPARATOR;
            return glob($sqlFolder . '*.sql', GLOB_NOESCAPE);
        }

        return false;
    }

    private function migrateExec($file) 
    {
        if ($strQuery = file_get_contents($file)) {

            $count = $this->dbConn->exec($strQuery);
            if ($count !== false) {
                $baseName = basename($file);

                $stmt = $this->dbConn->prepare('INSERT INTO ' . $this->dbTableVersion . ' (name) VALUES(?)');
                $stmt->execute([$baseName]);

                return true;
            }
        }

        return false;
    }

    public function migrate() 
    {
        $files = $this->getMigrationFiles();

        $resText = '';
        if (empty($files)) {
            $resText .= 'Ваша база данных в актуальном состоянии.';
        } else {
            $resText .= 'Начинаем миграцию...' . PHP_EOL . PHP_EOL;
        
            foreach ($files as $file) {
                if ($this->migrateExec($file)) {
                    $resText .= basename($file) . PHP_EOL;
                }
            }
        
            $resText .= PHP_EOL . 'Миграция завершена.';    
        }

        return $resText;
    }

    private function createTableVersions()
    {
        $strQuery = 'create table if not exists `' . $this->dbTableVersion . '` (
                `id` int(10) unsigned not null auto_increment,
                `name` varchar(255) not null,
                `created` timestamp default current_timestamp,
                primary key (id)
            )
            engine = innodb
            auto_increment = 1
            character set utf8
            collate utf8_unicode_ci;';
        
        if ($this->dbConn->exec($strQuery) !== false) {
            return true;
        } 

        return false;
    }
}