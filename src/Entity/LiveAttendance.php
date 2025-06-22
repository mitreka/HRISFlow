<?php

declare(strict_types=1);

namespace KejawenLab\Application\SemartHris\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Blameable\Traits\BlameableEntity;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use KejawenLab\Application\SemartHris\Component\Employee\Model\EmployeeInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity()
 * @ORM\Table(name="live_attendances")
 */
class LiveAttendance
{
    use BlameableEntity;
    use SoftDeleteableEntity;
    use TimestampableEntity;

    /**
     * @Groups({"read"})
     *
     * @ORM\Id()
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\\Uuid\\Doctrine\\UuidGenerator")
     */
    private $id;

    /**
     * @Groups({"write", "read"})
     *
     * @ORM\ManyToOne(targetEntity="KejawenLab\\Application\\SemartHris\\Entity\\Employee", fetch="EAGER")
     * @ORM\JoinColumn(name="employee_id", referencedColumnName="id")
     */
    private $employee;

    /**
     * @Groups({"read", "write"})
     *
     * @ORM\Column(type="datetime")
     */
    private $capturedAt;

    /**
     * @Groups({"read", "write"})
     *
     * @ORM\Column(type="string")
     */
    private $type;

    /**
     * @Groups({"read", "write"})
     *
     * @ORM\Column(type="string")
     */
    private $photoPath;

    /**
     * @Groups({"read", "write"})
     *
     * @ORM\Column(type="decimal", precision=10, scale=7)
     */
    private $latitude;

    /**
     * @Groups({"read", "write"})
     *
     * @ORM\Column(type="decimal", precision=10, scale=7)
     */
    private $longitude;

    public function getId(): string
    {
        return (string) $this->id;
    }

    public function getEmployee(): ?EmployeeInterface
    {
        return $this->employee;
    }

    public function setEmployee(?EmployeeInterface $employee): void
    {
        $this->employee = $employee;
    }

    public function getCapturedAt(): ?\DateTimeInterface
    {
        return $this->capturedAt;
    }

    public function setCapturedAt(?\DateTimeInterface $capturedAt): void
    {
        $this->capturedAt = $capturedAt;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): void
    {
        $this->type = $type;
    }

    public function getPhotoPath(): ?string
    {
        return $this->photoPath;
    }

    public function setPhotoPath(?string $photoPath): void
    {
        $this->photoPath = $photoPath;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(?float $latitude): void
    {
        $this->latitude = $latitude;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(?float $longitude): void
    {
        $this->longitude = $longitude;
    }
}
