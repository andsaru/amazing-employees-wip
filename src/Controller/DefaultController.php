<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\EmployeeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

// AbstractController es un controlador de Symfony
// que pone a disposición nuestra multitud de caracteristicas.
class DefaultController extends AbstractController
{

    /**
     * @Route("/default", name="default_index")
     *
     * La clase ruta debe estar precedida en los comentario por una arroba.
     * El primer parámetro de Route es la URL a la que queremos asociar la acción.
     * El segundo parámetro de Route es el nombre que queremos dar a la ruta.
     */

    // public function index(): Response // lo sustituye lo de abajo
    public function index(EmployeeRepository $employeeRepository): Response

    {
        // Una acción siempre debe devolver una respesta.
        // Por defecto deberá ser un objeto de la clase,
        // Symfony\Component\HttpFoundation\Response
        
        // render() es un método hereado de AbstractController
        // que devuelve el contenido declarado en una plantillas de Twig.
        // https://twig.symfony.com/doc/3.x/templates.html

        // symfony console
        // es un comando equivalente a
        // php bin/console

        $name = 'Andrés';

        // Mostrar las rutas disonibles en mi navegador:
        // - symfony console debug:router
        // - symfony console debug:router default_index
        // - symfony console router --help
        // - symfony console router:match /

        // Acceso y propiedades del objeto Request.
        // public function index(Request $solicitud): Response
        // {
        // https://symfony.com/doc/current/controller.html#the-request-and-response-object
        // echo '<pre>query: '; var_dump($request->query); echo '</pre>'; // Equivalente a $_GET, pero supervitaminado.
        // echo '<pre>post: '; var_dump($request->request); echo '</pre>'; // Equivalente a $_POST, pero supervitaminado.
        // echo '<pre>server: '; var_dump($request->server); echo '</pre>'; // Equivalente a $_SERVER, pero supervitaminado.
        // echo '<pre>files: '; var_dump($request->files); echo '</pre>'; // Equivalente a $_FILES, pero supervitaminado.
        // echo '<pre>idioma prefererido: '; var_dump($request->getPreferredLanguage()); echo '</pre>';

        // Metodo 1: accediendo al rpositorio a través de AbstractController.
        // $people = $this->getDoctrine()->getRepository(Employee::class)->findAll(); // Employee::class = App\Entity\Employee

        // Metodo 2: creando un parámetro indicando el tipo (type hint).
        $people = $employeeRepository->findAll(); // Employee::class = App\Entity\Employee

        // Se recomienda ponerlo siempre en Templates
        return $this->render('default/index.html.twig', [
            'people' => $people
        ]);
    }
    
    /**
     * @Route("/hola", name="default_hola")
     */
    public function hola(): Response
    {
        return new Response('<html><body>hola</body></html>');
    }

    /**
    * @Route("/adios", name="default_adios")
    */
    public function adios(): Response
    {
        return new Response('<html><body>adios</body></html>');
    }

    // Devuelve contenido JSON
    /**
          * @Route(
     *      "/default.{_format}",
     *      name="default_index_json",
     *      requirements = {
     *          "_format": "json"
     *      }
     * )
     * *
     * El comando:
     * symfony console router:match /default.json
     * buscará la acción coincidente con la ruta indicada
     * y mostrará la información asociada.
     */
    // Aquí mostraba toda la info de los usuarios con Json
    // public function indexJson(): JsonResponse
    // {
    //     //return new JsonResponse(self::PEOPLE);
    //     return $this->json(self::PEOPLE);
    // }

    // EJERCICIO
    // Crear el recurso para obtener una representación
    // de "UN" empleado en formato JSON.

    // Hemos inyectado un parametro de tipo request, indicandole el tipo de objeto que quiero agregar
    // Request tiene una propiedad query, equivalente a la variable GET
    // Query tiene varios metodos, uno de ellos es has, y le pregunta si tiene un parametro id
    // Si la evaluación de la primera expresión es falso devuelve self::PEOPLE (El array completo con todos los registros)
    // Si la evaluación es true devuelve, acceder primero al array, pero coger a la fila que le llega por id
    // self::PEOPLE[$request->query->get('id')]
    // Y devuelve el valor de la variabla $data
    // En index.html.twig le pasamos lo siguiente
    // <li><a href="{{ path('default_index_json', { _format: 'json',  id: loop.index0 }) }}">Ver en formato JSON</a></li>

    //public function indexJson(Request $request): JsonResponse {
                // Hace una comparación ternaria 
        //$data = $request->query->has('id') ? self::PEOPLE[$request->query->get('id')] : self::PEOPLE;
        //$data = $request->query->has('id') ? [] : []; // sustituye abajo que ya es de la BD
    public function indexJson(Request $request, EmployeeRepository $employeeRepository): JsonResponse { // Ya lo cogemos de la BD

        $data = $request->query->has('id') ? 
            $employeeRepository->find($request->query->get('id')) :
            $employeeRepository->findAll();

            return $this->json($data);
    }

    /**
     * @Route(
     *      "/default/{id}",
     *      name="default_show",
     *      requirements = {
     *          "id": "\d+"
     *      }
     * )
     */
    public function show(int $id, EmployeeRepository $employeeRepository): Response {
        $data = $employeeRepository->find($id);

        return $this->render('default/show.html.twig', [
            'id' => $id,
            'person' => $data
        ]);
    }

    /**
     * @Route("/redirect-to-home", name="default_redirect_to_home")
     */
    public function redirectToHome(): RedirectResponse
    {
        // Redirigir a la URL /
        // return $this->redirect('/');

        // Redirigir a una ruta utilizando su nombre.
        // return $this->redirectToRoute('default_show', ['id' => 1]);

        // Devolver directamente un objeto RedirectResponse.
        return new RedirectResponse('/', Response::HTTP_TEMPORARY_REDIRECT);
    }


}