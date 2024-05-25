<?php

namespace App\Controller;

use App\Entity\Classes;
use App\Entity\FinalGrade;
use App\Repository\ClassesRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class TeacherClassesController extends AbstractController
{
    #[Route('/api/teachers/classes', name: 'get_classes', methods: ['GET'])]
    public function getClasses(ClassesRepository $classesRepository): JsonResponse
    {
        $classes = $classesRepository->findAll();
        $responseData = [];
    foreach ($classes as $class) {
        $responseData[] = [
            'id' => $class->getId(),
            'name' => $class->getName(),
        ];
    }
    
        return $this->json($responseData, 200);
    }

    #[Route('/api/teachers/classes/{id}', name: 'get_class', methods: ['GET'])]
    public function getClassById(int $id, ClassesRepository $classesRepository, SerializerInterface $serializer): JsonResponse
    {
        $class = $classesRepository->find($id);
    
        if (!$class) {
            return $this->json(['error' => 'Class not found'], Response::HTTP_NOT_FOUND);
        }
    
        // Pobierz identyfikatory studentów
        $studentIds = $class->getStudentsId()->map(function ($student) {
            return $student->getId();
        });
    
        $responseData = [
            'id' => $class->getId(),
            'name' => $class->getName(),
            'student_ids' => $studentIds,
        ];
    
        return $this->json($responseData, 200);
    }

    #[Route('/api/teachers/classes', name: 'create_class', methods: ['POST'])]
    public function createClass(Request $request, EntityManagerInterface $em, UserRepository $userRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $teacher = $userRepository->find($data['teacher_id']);

        if (!$teacher || $teacher->getStatus() !== 1) {
            return $this->json(['error' => 'Invalid teacher'], Response::HTTP_BAD_REQUEST);
        }

        $class = new Classes();
        $class->setName($data['name'])
            ->setTeacherId($teacher)
            ->setActive(true)
            ->setCapacity($data['capacity']);

        $em->persist($class);
        $em->flush();

        return $this->json($class, Response::HTTP_CREATED);
    }

    #[Route('/api/teachers/classes/{id}', name: 'update_class', methods: ['PUT'])]
    public function updateClass(int $id, Request $request, ClassesRepository $classesRepository, EntityManagerInterface $em): JsonResponse
    {
        $class = $classesRepository->find($id);

        if (!$class) {
            return $this->json(['error' => 'Class not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        if (isset($data['name'])) $class->setName($data['name']);
        if (isset($data['capacity'])) $class->setCapacity($data['capacity']);
        if (isset($data['active'])) $class->setActive($data['active']);

        $em->flush();

        return $this->json($class);
    }

    #[Route('/api/teachers/classes/{id}', name: 'delete_class', methods: ['DELETE'])]
    public function deleteClass(int $id, ClassesRepository $classesRepository, EntityManagerInterface $em): JsonResponse
    {
        $class = $classesRepository->find($id);

        if (!$class) {
            return $this->json(['error' => 'Class not found'], Response::HTTP_NOT_FOUND);
        }

        $em->remove($class);
        $em->flush();

        return $this->json(['message' => 'Class deleted'], Response::HTTP_NO_CONTENT);
    }

    #[Route('/api/teachers/classes/{id}/students', name: 'get_students', methods: ['GET'])]
public function getStudents(int $id, ClassesRepository $classesRepository): JsonResponse
{
    $class = $classesRepository->find($id);

    if (!$class) {
        return $this->json(['error' => 'Class not found'], Response::HTTP_NOT_FOUND);
    }

    // Pobierz studentów
    $students = $class->getStudentsId();

    // Przekształć studentów na tablicę zawierającą imiona i nazwiska
    $formattedStudents = [];
    foreach ($students as $student) {
        $formattedStudents[$student->getId()] = [
            'first_name' => $student->getName(),
            'last_name' => $student->getSurname()
        ];
    }

    return $this->json($formattedStudents, 200);
}

    #[Route('/api/teachers/classes/{id}/final-grades', name: 'get_final_grades', methods: ['GET'])]
    public function getFinalGrades(int $id, ClassesRepository $classesRepository): JsonResponse
{
    $class = $classesRepository->find($id);

    if (!$class) {
        return $this->json(['error' => 'Class not found'], Response::HTTP_NOT_FOUND);
    }

    $finalGrades = $class->getFinalGrades();

    // Zbierz identyfikatory i oceny końcowe studentów
    $formattedFinalGrades = [];
    foreach ($finalGrades as $finalGrade) {
        $studentId = $finalGrade->getStudentId()->getId();
        $formattedFinalGrades[] = [
            'student_id' => $studentId,
            'final_grade' => $finalGrade->getGrade()
        ];
    }

    return $this->json($formattedFinalGrades, 200);
}

    #[Route('/api/teachers/classes/{classId}/students/{studentId}/grade', name: 'add_final_grade', methods: ['POST'])]
    public function addFinalGrade(int $classId, int $studentId, Request $request, EntityManagerInterface $em, ClassesRepository $classesRepository, UserRepository $userRepository): JsonResponse
    {
        $class = $classesRepository->find($classId);
        $student = $userRepository->find($studentId);

        if (!$class || !$student) {
            return $this->json(['error' => 'Class or student not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        $grade = new FinalGrade();
        $grade->setClassId($class)
            ->setStudentId($student)
            ->setGrade($data['grade']);

        $em->persist($grade);
        $em->flush();

        return $this->json($grade, Response::HTTP_CREATED);
    }
}