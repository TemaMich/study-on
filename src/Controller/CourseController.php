<?php

namespace App\Controller;

use App\Entity\Course;
use App\Form\CourseType;
use App\Repository\CourseRepository;
use App\Service\BillingClient;
use App\Service\BillingCourse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/course")
 */
class CourseController extends AbstractController
{
    private BillingCourse $billingCourse;
    private BillingClient $billingClient;

    public function __construct(BillingCourse $billingCourse, BillingClient $billingClient)
    {
        $this->billingCourse = $billingCourse;
        $this->billingClient = $billingClient;
    }
    /**
     * @Route("/", name="course_index", methods={"GET"})
     */
    public function index(CourseRepository $courseRepository): Response
    {
            $coursesInformation = $this->billingCourse->getCourses();

            $coursesCode = [];
            foreach($coursesInformation as $courseInf){
                $coursesCode[] = $courseInf['code'];
            }

            $courses = $courseRepository->findBy(["code" => $coursesCode]);



            return $this->render('course/index.html.twig', [
                'courses' => $courses,

            ]);
    }

    /**
     * @Route("/new", name="course_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $course = new Course();
        $form = $this->createForm(CourseType::class, null);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $course->setName($form->get('name')->getData());
            $course->setDescription($form->get('description')->getData());
            $course->setCode($form->get('code')->getData());

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($course);
            $entityManager->flush();

            $data = [
                "token" => $this->getUser()->getApiToken(),
                "code" => $form->get('code')->getData(),
                "price" => $form->get('price')->getData(),
                "type" => $form->get('type')->getData(),
            ];

            $error = $this->billingCourse->courseCreate($data);

            return $this->redirectToRoute('course_index');
        }

        return $this->render('course/new.html.twig', [
            'course' => $course,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="course_show", methods={"GET"})
     */
    public function show(Course $course): Response
    {
        $courseInformation = $this->billingCourse->getCourseByCode($course->getCode());
        if(!isset($courseInformation['code'])){
            return $this->redirectToRoute('course_index');
        }

        $disabled = true;
        $payInformations = false;
        if($this->getUser()){
            $payInformations = $this->billingCourse->getTransactions($this->getUser()->getApiToken(), $course->getCode());
            $balance = $this->billingClient->getBalance($this->getUser());
            if($courseInformation['price'] < $balance){
                $disabled = false;
            }
        }


        return $this->render('course/show.html.twig', [
            'course' => $course,
            'pay' => $payInformations,
            'disable' => $disabled,
        ]);
    }

    /**
     * @Route("/pay/{id}", name="course_pay", methods={"GET","POST"})
     */
    public function payCourse(Course $course): Response
    {
        $payInformation = $this->billingCourse->payCourse($this->getUser()->getApiToken(), $course->getCode());
        if(!isset($payInformation['error'])){
            return $this->redirectToRoute('course_show', ['id' => $course->getId()]);
        }
        return $this->redirectToRoute('course_show', ['id' => $course->getId()]);
    }


    /**
     * @Route("/edit/{id}", name="course_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Course $course): Response
    {
        $form = $this->createForm(CourseType::class, $course);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('course_show', ['id' => $course->getId()]);
        }

        return $this->render('course/edit.html.twig', [
            'course' => $course,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/delete/{id}", name="course_delete", methods={"POST"})
     */
    public function delete(Request $request, Course $course): Response
    {
        if ($this->isCsrfTokenValid('delete'.$course->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($course);
            $entityManager->flush();
            $this->billingCourse->deleteCourse($this->getUser()->getApiToken(), $course->getCode());
        }

        return $this->redirectToRoute('course_index');
    }
}
