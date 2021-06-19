<?php

namespace App\Controller;

use App\Entity\Employee;
use App\Repository\EmployeeRepository;
use App\Service\EmployeeNormalize;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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
    public function index(Request $request, EmployeeRepository $employeeRepository, EmployeeNormalize $employeeNormalize): Response    {
        if($request->query->has('term')) {
            $result = $employeeRepository->findByTerm($request->query->get('term'));

            $data = [];

            foreach($result as $employee) {
                $data[] = $employeeNormalize->employeeNormalize($employee);   
            }

            return $this->json($data);
        }

        $result = $employeeRepository->findAll();

        $data = [];

        foreach($result as $employee) {
            $data[] = $employeeNormalize->employeeNormalize($employee);   
        }

        return $this->json($data);
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
    public function show(
        Employee $employee,
        EmployeeNormalize $employeeNormalize
    ): Response    
    {
        return $this->json($employeeNormalize->employeeNormalize($employee));
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
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ): Response {
        $data = $request->request;

        $employee = new Employee();

        $employee->setName($data->get('name'));
        $employee->setEmail($data->get('email'));
        $employee->setAge($data->get('age'));
        $employee->setCity($data->get('city'));
        $employee->setPhone($data->get('phone'));

        $error = $validator->validate($employee);

        $entityManager->persist($employee);

        $errors = $validator->validate($employee);

        if (count($errors) > 0) {
            $dataErrors = [];

            /** @var \Symfony\Component\Validator\ConstraintViolation $error */
            foreach ($errors as $error) {
                $dataErrors[] = $error->getMessage();
            }

            return $this->json([
                'status' => 'error',
                'data' => [
                    'errors' => $dataErrors
                    ]
                ],
                Response::HTTP_BAD_REQUEST);
        } 

        // Hasta aquí $employee no tiene id.

        $entityManager->flush();

        //dump($employee);

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
    public function update(
        int $id,
        EntityManagerInterface $entityManager,
        EmployeeRepository $employeeRepository,
        Request $request
    ): Response
    {
        $employee = $employeeRepository->find($id);

        if(!$employee) {
            return $this->json([
                'message' => sprintf('No he encontrado el empleado con id.: %s', $id)
            ], Response::HTTP_NOT_FOUND);
        }
        $data = $request->request;

        $employee->setName($data->get('name'));
        $employee->setEmail($data->get('email'));
        $employee->setAge($data->get('age'));
        $employee->setCity($data->get('city'));
        $employee->setPhone($data->get('phone'));

        $entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
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