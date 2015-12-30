<?php

namespace Symplify\CodingStandard\Tests\Sniffs\Naming\InterfaceName;

use PHPUnit_Framework_TestCase;
use Symplify\CodingStandard\Tests\CodeSnifferRunner;


/**
 * @covers SymplifyCodingStandard\Sniffs\Naming\InterfaceNameSniff
 */
final class InterfaceNameSniffTest extends PHPUnit_Framework_TestCase
{

	public function testDetection()
	{
		$codeSnifferRunner = new CodeSnifferRunner('SymplifyCodingStandard.Naming.InterfaceName');

		$this->assertSame(0, $codeSnifferRunner->getErrorCountInFile(__DIR__ . '/correct.php'));
		$this->assertSame(0, $codeSnifferRunner->getErrorCountInFile(__DIR__ . '/correct2.php'));
		$this->assertSame(1, $codeSnifferRunner->getErrorCountInFile(__DIR__ . '/wrong.php'));
		$this->assertSame(1, $codeSnifferRunner->getErrorCountInFile(__DIR__ . '/wrong2.php'));
		$this->assertSame(1, $codeSnifferRunner->getErrorCountInFile(__DIR__ . '/wrong3.php'));
		$this->assertSame(1, $codeSnifferRunner->getErrorCountInFile(__DIR__ . '/wrong4.php'));
		$this->assertSame(1, $codeSnifferRunner->getErrorCountInFile(__DIR__ . '/wrong5.php'));
	}

}
