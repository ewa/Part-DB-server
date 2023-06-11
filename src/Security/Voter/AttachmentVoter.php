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

use Symfony\Bundle\SecurityBundle\Security;
use App\Entity\Attachments\AttachmentContainingDBElement;
use App\Entity\Attachments\Attachment;
use App\Entity\Attachments\AttachmentTypeAttachment;
use App\Entity\Attachments\CategoryAttachment;
use App\Entity\Attachments\CurrencyAttachment;
use App\Entity\Attachments\FootprintAttachment;
use App\Entity\Attachments\GroupAttachment;
use App\Entity\Attachments\ManufacturerAttachment;
use App\Entity\Attachments\MeasurementUnitAttachment;
use App\Entity\Attachments\PartAttachment;
use App\Entity\Attachments\ProjectAttachment;
use App\Entity\Attachments\StorelocationAttachment;
use App\Entity\Attachments\SupplierAttachment;
use App\Entity\Attachments\UserAttachment;
use App\Entity\UserSystem\User;
use App\Services\UserSystem\PermissionManager;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;

use function in_array;

class AttachmentVoter extends ExtendedVoter
{
    public function __construct(PermissionManager $resolver, EntityManagerInterface $entityManager, protected Security $security)
    {
        parent::__construct($resolver, $entityManager);
    }

    /**
     * Similar to voteOnAttribute, but checking for the anonymous user is already done.
     * The current user (or the anonymous user) is passed by $user.
     *
     * @param  string  $attribute
     */
    protected function voteOnUser(string $attribute, $subject, User $user): bool
    {
        //return $this->resolver->inherit($user, 'attachments', $attribute) ?? false;

        //This voter only works for attachments
        if (!is_a($subject, Attachment::class, true)) {
            return false;
        }

        if ($attribute === 'show_private') {
            return $this->resolver->inherit($user, 'attachments', 'show_private') ?? false;
        }


        if (is_object($subject)) {
            //If the attachment has no element (which should not happen), we deny access, as we can not determine if the user is allowed to access the associated element
            $target_element = $subject->getElement();
            if ($target_element instanceof AttachmentContainingDBElement) {
                return $this->security->isGranted($this->mapOperation($attribute), $target_element);
            }
        }

        if (is_string($subject)) {
            //If we do not have a concrete element (or we just got a string as value), we delegate to the different categories
            if (is_a($subject, AttachmentTypeAttachment::class, true)) {
                $param = 'attachment_types';
            } elseif (is_a($subject, CategoryAttachment::class, true)) {
                $param = 'categories';
            } elseif (is_a($subject, CurrencyAttachment::class, true)) {
                $param = 'currencies';
            } elseif (is_a($subject, ProjectAttachment::class, true)) {
                $param = 'projects';
            } elseif (is_a($subject, FootprintAttachment::class, true)) {
                $param = 'footprints';
            } elseif (is_a($subject, GroupAttachment::class, true)) {
                $param = 'groups';
            } elseif (is_a($subject, ManufacturerAttachment::class, true)) {
                $param = 'manufacturers';
            } elseif (is_a($subject, MeasurementUnitAttachment::class, true)) {
                $param = 'measurement_units';
            } elseif (is_a($subject, PartAttachment::class, true)) {
                $param = 'parts';
            } elseif (is_a($subject, StorelocationAttachment::class, true)) {
                $param = 'storelocations';
            } elseif (is_a($subject, SupplierAttachment::class, true)) {
                $param = 'suppliers';
            } elseif (is_a($subject, UserAttachment::class, true)) {
                $param = 'users';
            } elseif ($subject === Attachment::class) {
                //If the subject was deleted, we can not determine the type properly, so we just use the parts permission
                $param = 'parts';
            }
            else {
                throw new RuntimeException('Encountered unknown Parameter type: ' . (is_object($subject) ? $subject::class : $subject));
            }

            return $this->resolver->inherit($user, $param, $this->mapOperation($attribute)) ?? false;
        }

        return false;
    }

    private function mapOperation(string $attribute): string
    {
        return match ($attribute) {
            'read', 'view' => 'read',
            'edit', 'create', 'delete' => 'edit',
            'show_history' => 'show_history',
            default => throw new \RuntimeException('Encountered unknown attribute "'.$attribute.'" in AttachmentVoter!'),
        };
    }

    /**
     * Determines if the attribute and subject are supported by this voter.
     *
     * @param  string  $attribute An attribute
     * @param mixed  $subject   The subject to secure, e.g. an object the user wants to access or any other PHP type
     *
     * @return bool True if the attribute and subject are supported, false otherwise
     */
    protected function supports(string $attribute, $subject): bool
    {
        if (is_a($subject, Attachment::class, true)) {
            //These are the allowed attributes
            return in_array($attribute, ['read', 'view', 'edit', 'delete', 'create', 'show_private', 'show_history'], true);
        }

        //Allow class name as subject
        return false;
    }
}
