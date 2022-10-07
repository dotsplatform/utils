<?php
/**
 * Description of BaseObjectTest.php.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Yehor Herasymchuk <yehor@dotsplatform.com>
 */

namespace Dots\Tests\Dots\BaseObject;

use Tests\TestCase;
use Dots\Tests\Dots\BaseObject\Objects\TestBaseObject;

class BaseObjectTest extends TestCase
{
    /**
     * @group unit
     * @group object
     */
    public function testCreatesObject(): void
    {
        $data = [
            'id' => $this->generateUuid(),
            'name' => $this->generateUuid(),
        ];
        $object = TestBaseObject::fromArray($data);
        $this->assertEquals($data['id'], $object->getId());
        $this->assertEquals($data['name'], $object->getName());
    }

    /**
     * @group unit
     * @group object
     */
    public function testSetDefaultPropertyValue(): void
    {
        $object = TestBaseObject::fromArray([
            'id' => $this->generateUuid(),
        ]);
        $this->assertEquals('Test', $object->getName());
    }
}
