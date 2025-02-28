<?php
/**
 * This file is part of Part-DB (https://github.com/Part-DB/Part-DB-symfony).
 *
 * Copyright (C) 2019 - 2022 Jan Böhmer (https://github.com/jbtronics)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Parts\Part;
use App\Entity\UserSystem\User;

/**
 * A Voter that votes on Part entities.
 *
 * See parts permissions for valid operations.
 */
class PartVoter extends ExtendedVoter
{
    final public const READ = 'read';

    protected function supports($attribute, $subject): bool
    {
        if (is_a($subject, Part::class, true)) {
            return $this->resolver->isValidOperation('parts', $attribute);
        }

        //Allow class name as subject
        return false;
    }

    protected function voteOnUser(string $attribute, $subject, User $user): bool
    {
        //Null concealing operator means, that no
        return $this->resolver->inherit($user, 'parts', $attribute) ?? false;
    }
}
