<?php

use Selwer\Migration\ConnectBD; 
use PHPUnit\Framework\TestCase;

class MigrationTest extends TestCase
{

    protected function setUp()
    {
        $this->file1 = __DIR__ . DIRECTORY_SEPARATOR . 'sql1.sql';
        $this->file2 = __DIR__ . DIRECTORY_SEPARATOR . 'sql2.sql';
    }

    protected function tearDown()
    {
        $db = ConnectBD::getInstance('localhost', 'test', 'root', '');
        $conn = $db->getConnection();
        $strQuery = 'drop table test_migration2;';
        
        $conn->exec($strQuery);

        if (file_exists($this->file1)) {
            unlink($this->file1);
        }
        if (file_exists($this->file2)) {
            unlink($this->file2);
        }
    }

    public function testCreateTableVersion()
    {
        $class = new ReflectionClass('\Selwer\Migration\Init');
        $method = $class->getMethod('createTableVersions');
        $method->setAccessible(true);

        $migration = $this->getMockBuilder('\Selwer\Migration\Init')
            ->setConstructorArgs([__DIR__, 'localhost', 'test', 'root', '', 'test_migration2'])
            ->getMock();

        $results = $method->invoke($migration);
        $this->assertTrue($results);
    }

    
    public function testGetMigrationFiles()
    {
        $class = new ReflectionClass('\Selwer\Migration\Init');
        $method = $class->getMethod('getMigrateAllFiles');
        $method->setAccessible(true);

        $migration = $this->getMockBuilder('\Selwer\Migration\Init')
            ->setConstructorArgs([__DIR__, 'localhost', 'test', 'root', '', 'test_migration2'])
            ->getMock();
        

        $fp = fopen($this->file1, 'w');
        fwrite($fp, 'sql1');
        fclose($fp);

        $fp = fopen($this->file2, 'w');
        fwrite($fp, 'sql2');
        fclose($fp);

        $results = $method->invoke($migration);
        $this->assertSame([$this->file1, $this->file2], $results);
    }
}