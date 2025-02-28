<?php

declare(strict_types=1);

/*
 * This file is part of Part-DB (https://github.com/Part-DB/Part-DB-symfony).
 *
 *  Copyright (C) 2019 - 2023 Jan Böhmer (https://github.com/jbtronics)
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as published
 *  by the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */
namespace App\Serializer;

use Brick\Math\BigNumber;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @see \App\Tests\Serializer\BigNumberNormalizerTest
 */
class BigNumberNormalizer implements NormalizerInterface
{

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof BigNumber;
    }

    public function normalize($object, string $format = null, array $context = []): string
    {
        if (!$object instanceof BigNumber) {
            throw new \InvalidArgumentException('This normalizer only supports BigNumber objects!');
        }

        return (string) $object;
    }

    /**
     * @return bool[]
     */
    public function getSupportedTypes(?string $format): array
    {
        return [
            BigNumber::class => true,
        ];
    }
}
