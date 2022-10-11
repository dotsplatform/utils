<?php
/**
 * Description of RequestSignatureGeneratorTest.php.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Yehor Herasymchuk <yehor@dotsplatform.com>
 */

namespace Tests\Unit\Utils;

use Dots\Utils\RequestSignatureGenerator;
use Tests\TestCase;

class RequestSignatureGeneratorTest extends TestCase
{
    private function getRequestSignatureGenerator(): RequestSignatureGenerator
    {
        return new RequestSignatureGenerator();
    }

    /**
     * @group unit
     */
    public function testGenerate(): void
    {
        $data = [
            'test' => $this->generateUuid(),
        ];
        $key = $this->generateUuid();
        $signature = $this->getRequestSignatureGenerator()->generate($key, $data);
        $this->assertNotEmpty($signature);
    }

    /**
     * @group unit
     */
    public function testGeneratesForEmptyData(): void
    {
        $data = [];
        $key = $this->generateUuid();
        $signature = $this->getRequestSignatureGenerator()->generate($key, $data);
        $this->assertNotEmpty($signature);
    }

    /**
     * @group unit
     */
    public function testGenerateEqualsSameForSameData(): void
    {
        $data = [
            'test' => $this->generateUuid(),
        ];
        $key = $this->generateUuid();
        $signature1 = $this->getRequestSignatureGenerator()->generate($key, $data);
        $signature2 = $this->getRequestSignatureGenerator()->generate($key, $data);
        $this->assertEquals($signature1, $signature2);
    }

    /**
     * @group unit
     */
    public function testGenerateEqualsIfDataIFDataNotTheSameOrder(): void
    {
        $data1 = [
            'test1' => $this->generateUuid(),
            'test3' => $this->generateUuid(),
            'test2' => $this->generateUuid(),
        ];
        $data2 = [
            'test2' => $data1['test2'],
            'test3' => $data1['test3'],
            'test1' => $data1['test1'],
        ];
        $key = $this->generateUuid();
        $signature1 = $this->getRequestSignatureGenerator()->generate($key, $data1);
        $signature2 = $this->getRequestSignatureGenerator()->generate($key, $data2);
        $this->assertEquals($signature1, $signature2);
    }

    /**
     * @group unit
     */
    public function testGenerateNotEqualsIfKeysAreDiffrent(): void
    {
        $data = [
            'test1' => $this->generateUuid(),
        ];
        $key1 = $this->generateUuid();
        $key2 = $this->generateUuid();
        $signature1 = $this->getRequestSignatureGenerator()->generate($key1, $data);
        $signature2 = $this->getRequestSignatureGenerator()->generate($key2, $data);
        $this->assertNotEquals($signature1, $signature2);
    }
}
