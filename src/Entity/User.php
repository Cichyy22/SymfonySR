<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $name = null;

    #[ORM\Column(length: 50)]
    private ?string $surname = null;

    #[ORM\Column]
    private ?int $status = null;

    /**
     * @var Collection<int, Classes>
     */
    #[ORM\OneToMany(targetEntity: Classes::class, mappedBy: 'teacher_id')]
    private Collection $classes;

    /**
     * @var Collection<int, Classes>
     */
    #[ORM\ManyToMany(targetEntity: Classes::class, mappedBy: 'students_id')]
    private Collection $lessons;

    /**
     * @var Collection<int, FinalGrade>
     */
    #[ORM\OneToMany(targetEntity: FinalGrade::class, mappedBy: 'student_id')]
    private Collection $finalGrades;

    public function __construct()
    {
        $this->classes = new ArrayCollection();
        $this->lessons = new ArrayCollection();
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

    public function getSurname(): ?string
    {
        return $this->surname;
    }

    public function setSurname(string $surname): static
    {
        $this->surname = $surname;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): static
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection<int, Classes>
     */
    public function getClasses(): Collection
    {
        return $this->classes;
    }

    public function addClass(Classes $class): static
    {
        if (!$this->classes->contains($class)) {
            $this->classes->add($class);
            $class->setTeacherId($this);
        }

        return $this;
    }

    public function removeClass(Classes $class): static
    {
        if ($this->classes->removeElement($class)) {
            // set the owning side to null (unless already changed)
            if ($class->getTeacherId() === $this) {
                $class->setTeacherId(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Classes>
     */
    public function getLessons(): Collection
    {
        return $this->lessons;
    }

    public function addLesson(Classes $lesson): static
    {
        if (!$this->lessons->contains($lesson)) {
            $this->lessons->add($lesson);
            $lesson->addStudentsId($this);
        }

        return $this;
    }

    public function removeLesson(Classes $lesson): static
    {
        if ($this->lessons->removeElement($lesson)) {
            $lesson->removeStudentsId($this);
        }

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
            $finalGrade->setStudentId($this);
        }

        return $this;
    }

    public function removeFinalGrade(FinalGrade $finalGrade): static
    {
        if ($this->finalGrades->removeElement($finalGrade)) {
            // set the owning side to null (unless already changed)
            if ($finalGrade->getStudentId() === $this) {
                $finalGrade->setStudentId(null);
            }
        }

        return $this;
    }
}
