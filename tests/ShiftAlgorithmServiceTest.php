<?php

namespace Tests;

use Dev\Domain\Service\ShiftAlgorithmService;
use PHPUnit\Framework\TestCase;

final class ShiftAlgorithmServiceTest extends TestCase
{
    public function testShiftEncryptAlgorithmString()
    {
        $shiftAlgorithmService = new ShiftAlgorithmService();
        $this->assertEquals(
            "Khoor Zruog",
            $shiftAlgorithmService->encrypt("Hello World")
        );
    }

    public function testShiftEncryptAlgorithmChar()
    {
        $shiftAlgorithmService = new ShiftAlgorithmService();
        $this->assertEquals(
            "d",
            $shiftAlgorithmService->encrypt("a")
        );
    }
}