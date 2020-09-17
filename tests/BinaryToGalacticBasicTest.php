<?php

declare(strict_types=1);

namespace Tests;

use Alister\Bud\BinaryToGalacticBasic;
use PHPUnit\Framework\TestCase;
use RuntimeException;

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
     *
     * @param string $originalBinary
     * @param ?string $expectedOutput
     * @param bool|string $shouldFail false|Exception-class-name
     */
    public function testTranslate(string $originalBinary, ?string $expectedOutput, $shouldFail = false): void
    {
        if ($shouldFail) {
            $this->expectException($shouldFail);
        }
        $this->assertSame($expectedOutput, $this->sut->translate($originalBinary));
    }

    public function dpTranslateFromBinary(): array
    {
        return [
            'UPPERcase' => ['01000001 01010011 01000011 01001001 01001001', 'ASCII'],
            '2187' => ['01000011 01100101 01101100 01101100 00100000 00110010 00110001 00111000 00110111', 'Cell 2187'],
            'broken' => ['01007011 011101 01131 011xxxxx', null, RuntimeException::class],
        ];
    }
}
