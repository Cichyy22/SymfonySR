<?php

namespace App\Controller;

use App\Entity\Classes;
use App\Entity\User;
use App\Entity\FinalGrade;
use App\Repository\ClassesRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class StudentController extends AbstractController
{
    /**
     * @Route("/api/students/register", name="register_student", methods={"POST"})
     */
    #[Route('/api/students/register', name: 'register_student', methods: ['POST'])]
    public function registerStudent(Request $request, EntityManagerInterface $entityManager, ClassesRepository $classesRepository, UserRepository $userRepository): JsonResponse
{
    $requestData = json_decode($request->getContent(), true);

    $studentId = $requestData['student_id'] ?? null;
    $classId = $requestData['class_id'] ?? null;

    if (!$studentId || !$classId) {
        return $this->json(['error' => 'Student ID and Class ID are required'], Response::HTTP_BAD_REQUEST);
    }

    $student = $userRepository->find($studentId);
    $class = $classesRepository->find($classId);

    if (!$student || !$class) {
        return $this->json(['error' => 'Student or Class not found'], Response::HTTP_NOT_FOUND);
    }

    // Check if class is active
    if (!$class->isActive()) {
        return $this->json(['error' => 'Class is not active'], Response::HTTP_BAD_REQUEST);
    }

    // Check if the class is already full
    $studentsCount = $class->getStudentsId()->count();
    if ($studentsCount >= $class->getCapacity()) {
        return $this->json(['error' => 'Class is already full'], Response::HTTP_BAD_REQUEST);
    }

    // Add student to class
    $class->addStudentsId($student);
    $entityManager->flush();

    return $this->json(['message' => 'Student registered for class successfully'], Response::HTTP_CREATED);
}

    /**
     * @Route("/api/students/unregister/{student_id}/{class_id}", name="unregister_student", methods={"DELETE"})
     */
    #[Route('/api/students/unregister/{student_id}/{class_id}', name: 'unregister_student', methods: ['DELETE'])]
    public function unregisterStudent(int $student_id, int $class_id, EntityManagerInterface $entityManager, ClassesRepository $classesRepository, UserRepository $userRepository): JsonResponse
    {
        $student = $userRepository->find($student_id);
        $class = $classesRepository->find($class_id);

        if (!$student || !$class) {
            return $this->json(['error' => 'Student or Class not found'], Response::HTTP_NOT_FOUND);
        }

        $class->removeStudentsId($student);

        $entityManager->flush();

        return $this->json(['message' => 'Student unregistered from class successfully'], Response::HTTP_OK);
    }

    /**
     * @Route("/api/students/finalgrade/{student_id}/{class_id}", name="get_final_grade", methods={"GET"})
     */
    #[Route('/api/students/finalgrades/{student_id}', name: 'get_final_grades_for_student', methods: ['GET'])]
    public function getFinalGradesForStudent(int $student_id, EntityManagerInterface $entityManager): JsonResponse
    {
        $finalGradeRepository = $entityManager->getRepository(FinalGrade::class);
        $finalGrades = $finalGradeRepository->findBy(['student_id' => $student_id]);

        $finalGradeData = [];
        foreach ($finalGrades as $finalGrade) {
            $classId = $finalGrade->getClassId()->getId();
            $grade = $finalGrade->getGrade();
            $finalGradeData[] = ['class_id' => $classId, 'grade' => $grade];
        }

        return $this->json(['final_grades' => $finalGradeData], Response::HTTP_OK);
    }
}