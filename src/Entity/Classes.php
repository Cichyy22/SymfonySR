<?php

namespace App\Entity;

use App\Repository\ClassesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ClassesRepository::class)]
class Classes
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[ORM\ManyToOne(inversedBy: 'classes')]
    private ?User $teacher_id = null;

    #[ORM\Column]
    private ?bool $active = null;

    #[ORM\Column]
    private ?int $capacity = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'lessons')]
    private Collection $students_id;

    /**
     * @var Collection<int, FinalGrade>
     */
    #[ORM\OneToMany(targetEntity: FinalGrade::class, mappedBy: 'class_id')]
    private Collection $finalGrades;

    public function __construct()
    {
        $this->students_id = new ArrayCollection();
        $this->finalGrades = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getTeacherId(): ?User
    {
        return $this->teacher_id;
    }

    public function setTeacherId(?User $teacher_id): static
    {
        $this->teacher_id = $teacher_id;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;

        return $this;
    }

    public function getCapacity(): ?int
    {
        return $this->capacity;
    }

    public function setCapacity(int $capacity): static
    {
        $this->capacity = $capacity;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getStudentsId(): Collection
    {
        return $this->students_id;
    }

    public function addStudentsId(User $studentsId): static
    {
        if (!$this->students_id->contains($studentsId)) {
            $this->students_id->add($studentsId);
        }

        return $this;
    }

    public function removeStudentsId(User $studentsId): static
    {
        $this->students_id->removeElement($studentsId);

        return $this;
    }

    /**
     * @return Collection<int, FinalGrade>
     */
    public function getFinalGrades(): Collection
    {
        return $this->finalGrades;
    }

    public function addFinalGrade(FinalGrade $finalGrade): static
    {
        if (!$this->finalGrades->contains($finalGrade)) {
            $this->finalGrades->add($finalGrade);
            $finalGrade->setClassId($this);
        }

        return $this;
    }

    public function removeFinalGrade(FinalGrade $finalGrade): static
    {
        if ($this->finalGrades->removeElement($finalGrade)) {
            // set the owning side to null (unless already changed)
            if ($finalGrade->getClassId() === $this) {
                $finalGrade->setClassId(null);
            }
        }

        return $this;
    }
}
