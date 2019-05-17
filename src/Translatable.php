<?php
declare(strict_types=1);

namespace Alister\Bud;

interface Translatable
{
    public function translate(string $original): string;
}
