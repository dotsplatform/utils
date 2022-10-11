<?php
/**
 * Description of StrComparatorTest.php.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Yehor Herasymchuk <yehor@dotsplatform.com>
 */

namespace Tests\Unit\Utils;

use Dots\Utils\StrComparator;
use Tests\TestCase;

class StrComparatorTest extends TestCase
{
    /**
     * @group unit
     */
    public function testComparesStringReturnsMinusOne(): void
    {
        $a = 'chernigov';
        $b = 'kiev';

        $comparator = new StrComparator();
        $result = $comparator->compare($a, $b);

        $this->assertEquals(-1, $result);
    }

    /**
     * @group unit
     */
    public function testComparesReturnsGreater(): void
    {
        $a = 'kiev';
        $b = 'chernigov';

        $comparator = new StrComparator();
        $result = $comparator->compare($a, $b);

        $this->assertEquals(1, $result);
    }

    /**
     * @group unit
     */
    public function testComparesReturnsEquals(): void
    {
        $a = 'kiev';
        $b = 'kiev';

        $comparator = new StrComparator();
        $result = $comparator->compare($a, $b);

        $this->assertEquals(0, $result);
    }

    /**
     * @group unit
     */
    public function testComparesReturnsIsNotCaseSensetive(): void
    {
        $a = 'Kiev';
        $b = 'kiev';

        $comparator = new StrComparator();
        $result = $comparator->compare($a, $b);

        $this->assertEquals(0, $result);
    }

    /**
     * @group unit
     */
    public function testComparesReturnsIsNotCaseSensitive2(): void
    {
        $a = 'KIEV';
        $b = 'kiev';

        $comparator = new StrComparator();
        $result = $comparator->compare($a, $b);

        $this->assertEquals(0, $result);
    }

    /**
     * @group unit
     */
    public function testComparesUAReturnsVBeforeI(): void
    {
        $a = 'Вінниця';
        $b = 'Івано-фраківськ';

        $comparator = new StrComparator();
        $result = $comparator->compare($a, $b);

        $this->assertEquals(-1, $result);
    }
}
