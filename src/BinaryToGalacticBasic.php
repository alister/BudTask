<?php
declare(strict_types=1);

namespace Alister\Bud;

class BinaryToGalacticBasic implements Translatable
{
    public function translate(string $originalBinary): string
    {
        $characters = explode(' ', $originalBinary);

        $string = '';
        foreach ($characters as $character) {
            $lengthOfValidBinaryLetter = strspn($character, '01');
            if ($lengthOfValidBinaryLetter !== 8) {
                throw new \RuntimeException("binary message contains non-binary-ascii, {$character}");
            }

            $string .= chr(intval($character, 2));
        }

        return $string;
    }
}
