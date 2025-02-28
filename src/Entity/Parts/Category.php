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

namespace App\Entity\Parts;

use App\Entity\Attachments\Attachment;
use App\Entity\Attachments\AttachmentTypeAttachment;
use App\Repository\Parts\CategoryRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\Common\Collections\ArrayCollection;
use App\Entity\Attachments\CategoryAttachment;
use App\Entity\Base\AbstractPartsContainingDBElement;
use App\Entity\Base\AbstractStructuralDBElement;
use App\Entity\Parameters\CategoryParameter;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * This entity describes a category, a part can belong to, which is used to group parts by their function.
 *
 * @extends AbstractPartsContainingDBElement<CategoryAttachment, CategoryParameter>
 */
#[ORM\Entity(repositoryClass: CategoryRepository::class)]
#[ORM\Table(name: '`categories`')]
#[ORM\Index(name: 'category_idx_name', columns: ['name'])]
#[ORM\Index(name: 'category_idx_parent_name', columns: ['parent_id', 'name'])]
class Category extends AbstractPartsContainingDBElement
{
    #[ORM\OneToMany(targetEntity: self::class, mappedBy: 'parent')]
    #[ORM\OrderBy(['name' => 'ASC'])]
    protected Collection $children;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'children')]
    #[ORM\JoinColumn(name: 'parent_id')]
    protected ?AbstractStructuralDBElement $parent = null;

    /**
     * @var string
     */
    #[Groups(['full', 'import'])]
    #[ORM\Column(type: Types::TEXT)]
    protected string $partname_hint = '';

    /**
     * @var string
     */
    #[Groups(['full', 'import'])]
    #[ORM\Column(type: Types::TEXT)]
    protected string $partname_regex = '';

    /**
     * @var bool
     */
    #[Groups(['full', 'import'])]
    #[ORM\Column(type: Types::BOOLEAN)]
    protected bool $disable_footprints = false;

    /**
     * @var bool
     */
    #[Groups(['full', 'import'])]
    #[ORM\Column(type: Types::BOOLEAN)]
    protected bool $disable_manufacturers = false;

    /**
     * @var bool
     */
    #[Groups(['full', 'import'])]
    #[ORM\Column(type: Types::BOOLEAN)]
    protected bool $disable_autodatasheets = false;

    /**
     * @var bool
     */
    #[Groups(['full', 'import'])]
    #[ORM\Column(type: Types::BOOLEAN)]
    protected bool $disable_properties = false;

    /**
     * @var string
     */
    #[Groups(['full', 'import'])]
    #[ORM\Column(type: Types::TEXT)]
    protected string $default_description = '';

    /**
     * @var string
     */
    #[Groups(['full', 'import'])]
    #[ORM\Column(type: Types::TEXT)]
    protected string $default_comment = '';

    /**
     * @var Collection<int, CategoryAttachment>
     */
    #[Assert\Valid]
    #[Groups(['full'])]
    #[ORM\OneToMany(targetEntity: CategoryAttachment::class, mappedBy: 'element', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['name' => 'ASC'])]
    protected Collection $attachments;

    #[ORM\ManyToOne(targetEntity: CategoryAttachment::class)]
    #[ORM\JoinColumn(name: 'id_preview_attachment', onDelete: 'SET NULL')]
    protected ?Attachment $master_picture_attachment = null;

    /** @var Collection<int, CategoryParameter>
     */
    #[Assert\Valid]
    #[Groups(['full'])]
    #[ORM\OneToMany(targetEntity: CategoryParameter::class, mappedBy: 'element', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['group' => 'ASC', 'name' => 'ASC'])]
    protected Collection $parameters;

    public function getPartnameHint(): string
    {
        return $this->partname_hint;
    }

    public function setPartnameHint(string $partname_hint): self
    {
        $this->partname_hint = $partname_hint;

        return $this;
    }

    public function getPartnameRegex(): string
    {
        return $this->partname_regex;
    }

    public function setPartnameRegex(string $partname_regex): self
    {
        $this->partname_regex = $partname_regex;

        return $this;
    }

    public function isDisableFootprints(): bool
    {
        return $this->disable_footprints;
    }

    public function setDisableFootprints(bool $disable_footprints): self
    {
        $this->disable_footprints = $disable_footprints;

        return $this;
    }

    public function isDisableManufacturers(): bool
    {
        return $this->disable_manufacturers;
    }

    public function setDisableManufacturers(bool $disable_manufacturers): self
    {
        $this->disable_manufacturers = $disable_manufacturers;

        return $this;
    }

    public function isDisableAutodatasheets(): bool
    {
        return $this->disable_autodatasheets;
    }

    public function setDisableAutodatasheets(bool $disable_autodatasheets): self
    {
        $this->disable_autodatasheets = $disable_autodatasheets;

        return $this;
    }

    public function isDisableProperties(): bool
    {
        return $this->disable_properties;
    }

    public function setDisableProperties(bool $disable_properties): self
    {
        $this->disable_properties = $disable_properties;

        return $this;
    }

    public function getDefaultDescription(): string
    {
        return $this->default_description;
    }

    public function setDefaultDescription(string $default_description): self
    {
        $this->default_description = $default_description;

        return $this;
    }

    public function getDefaultComment(): string
    {
        return $this->default_comment;
    }

    public function setDefaultComment(string $default_comment): self
    {
        $this->default_comment = $default_comment;

        return $this;
    }
    public function __construct()
    {
        parent::__construct();
        $this->children = new ArrayCollection();
        $this->attachments = new ArrayCollection();
        $this->parameters = new ArrayCollection();
    }
}
