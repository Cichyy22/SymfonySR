<?php

namespace App\Entity;

use App\Repository\FinalGradeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FinalGradeRepository::class)]
class FinalGrade
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'finalGrades')]
    private ?Classes $class_id = null;

    #[ORM\Column(nullable: true)]
    private ?int $grade = null;

    #[ORM\ManyToOne(inversedBy: 'finalGrades')]
    private ?User $student_id = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getClassId(): ?Classes
    {
        return $this->class_id;
    }

    public function setClassId(?Classes $class_id): static
    {
        $this->class_id = $class_id;

        return $this;
    }

    public function getGrade(): ?int
    {
        return $this->grade;
    }

    public function setGrade(?int $grade): static
    {
        $this->grade = $grade;

        return $this;
    }

    public function getStudentId(): ?User
    {
        return $this->student_id;
    }

    public function setStudentId(?User $student_id): static
    {
        $this->student_id = $student_id;

        return $this;
    }
}
