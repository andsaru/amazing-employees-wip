<?php

namespace App\Controller;

use App\Entity\Employee;
use App\Repository\EmployeeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/amazing-employees", name="api_employees_")
 */
class ApiEmployeesController extends AbstractController
{
    /**
     * @Route(
     *      "",
     *      name="cget", 
     *      methods={"GET"}
     * )
     */
    public function index(Request $request, EmployeeRepository $employeeRepository): Response    {
        if($request->query->has('term')) {
            $people = $employeeRepository->findByTerm($request->query->get('term'));

            return $this->json($people);
        }

        return $this->json($employeeRepository->findAll());
    }
    
    /* Espera recibir por id cualquier número entero "/d+" */
    /**
     * @Route(
     *      "/{id}",
     *      name="get",
     *      methods={"GET"},
     *      requirements={
     *          "id": "\d+"
     *      }
     * )
     */
    public function show(int $id, EmployeeRepository $employeeRepository): Response    {
        $data = $employeeRepository->find($id);

        dump($id);
        dump($data);

        return $this->json($data);
    }

    /**
     * @Route(
     *      "",
     *      name="post",
     *      methods={"POST"}
     * )
     */
    public function add(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $data = $request->request;

        $employee = new Employee();

        $employee->setName($data->get('name'));
        $employee->setEmail($data->get('email'));
        $employee->setAge($data->get('age'));
        $employee->setCity($data->get('city'));
        $employee->setPhone($data->get('phone'));

        $entityManager->persist($employee);

        // Hasta aquí $employee no tiene id.

        $entityManager->flush();

        dump($employee);

        return  $this->json(
            $employee,
            Response::HTTP_CREATED,
            [
                'Location' => $this->generateUrl(
                    'api_employees_get',
                    [
                        'id' => $employee->getId()
                    ]
                )
            ]
        );
    }

    /**
     * @Route(
     *      "/{id}",
     *      name="put",
     *      methods={"PUT"},
     *      requirements={
     *          "id": "\d+"
     *      }
     * )
     */
    public function update(int $id): Response
    {
        return $this->json([
            'method' => 'PUT',
            'description' => 'Actualiza un recurso empleado con id: '.$id.'.',
        ]);
    }

    /**
     * @Route(
     *      "/{id}",
     *      name="delete",
     *      methods={"DELETE"},
     *      requirements={
     *          "id": "\d+"
     *      }
     * )
     */
    public function remove(
        int $id,
        EntityManagerInterface $entityManager,
        EmployeeRepository $employeeRepository
    ): Response
    {
        $employee = $employeeRepository->find($id);

        if(!$employee) {
            return $this->json([
                'message' => sprintf('No he encontrado el empleado con id.: %s ', $id)
            ], Response::HTTP_NOT_FOUND);
        }

        dump($employee);

        // remove() prepara el sistema pero NO ejecuta la sentencia
        $entityManager->remove($employee);
        $entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}