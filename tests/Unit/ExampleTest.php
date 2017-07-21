<?php

namespace Tests\Unit;

use Upload;
use Exception;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ExampleTest extends TestCase
{

    public function testPhpVersion()
    {
        $this->assertTrue(version_compare(phpversion(), '5.6.4', '>='));
    }

    public function testOpenSSL() {
        $this->assertTrue(extension_loaded('openssl'));
    }

    public function testPDO() {
        $this->assertTrue(extension_loaded('pdo'));
    }

    public function testMbString() {
        $this->assertTrue(extension_loaded('mbstring'));
    }

    public function testTokenizer() {
        $this->assertTrue(extension_loaded('tokenizer'));
    }

    public function testXml() {
        $this->assertTrue(extension_loaded('xml'));
    }

    public function testJson() {
        $this->assertTrue(extension_loaded('json'));
    }

    public function testZip() {
        $this->assertTrue(extension_loaded('zip'));
    }

    public function testCheckIpRange() {
        $ips = [
            true => [
                '192.168.0.10'  => '192.168.0.*',
                '10.0.2.1'      => '10.0.2.0-10.0.2.10',
                '191.57.177.24' => '191.57.177.0/24',
                '127.0.0.1'     => '127.0.0.1'

            ],

            false => [
                '192.168.2.10'  => '192.168.0.*',
                '10.0.2.11'      => '10.0.2.0-10.0.2.10',
                '191.57.171.24'  => '191.57.177.0/24',
                '127.0.0.1'     => '127.0.0.2'
            ]
        ];

        foreach ($ips as $assert => $tests) {
            foreach ($tests as $ip => $range) {
                if ($assert == true) {
                    $this->assertTrue(Upload::isValidIp($ip, $range), 'Test should be OK but is not (IP: '.$ip.', range '.$range.')');
                }
                else {
                    $this->assertFalse(Upload::isValidIp($ip, $range), 'Test should not be OK but is it (IP: '.$ip.', range '.$range.')');
                }
            }
        }
    }

    /**
     * @expectedException Exception
     */
    function testFilePath() {
        upload::generateFilePath('abc');
    }

    function testUploadFilesize() {
        $this->assertTrue(is_numeric(Upload::fileMaxSize()), 'Max filesize should be a numeric');
    }
}
