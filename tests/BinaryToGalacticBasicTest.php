<?php

namespace Alister\Bud;

use PHPUnit\Framework\TestCase;

class BinaryToGalacticBasicTest extends TestCase
{
    /** @var \Alister\Bud\BinaryToGalacticBasic */
    private $sut;

    protected function setUp(): void
    {
        $this->sut = new BinaryToGalacticBasic();
    }

    /**
     * @dataProvider dpTranslateFromBinary
     */
    public function testTranslate($originalBinary, $expectedOutput, $shouldFail = false)
    {
        if ($shouldFail) {
            $this->expectException($shouldFail);
        }
        $this->assertSame($expectedOutput, $this->sut->translate($originalBinary));
    }

    public function dpTranslateFromBinary()
    {
        return [
            'UPPERcase' => ['01000001 01010011 01000011 01001001 01001001', 'ASCII'],
            '2187' => ['01000011 01100101 01101100 01101100 00100000 00110010 00110001 00111000 00110111', 'Cell 2187'],
            'broken' => ['01007011 011101 01131 011xxxxx', null, \RuntimeException::class],
        ];
    }
}
