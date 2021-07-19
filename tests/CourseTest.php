<?php

namespace App\Tests;

use App\DataFixtures\CourseFixtures;
use App\Entity\Course;
use App\Entity\Lesson;

class CourseTest extends AbstractTest
{
    protected function getFixtures(): array
    {
        return [CourseFixtures::class];
    }

    public function testCountGet(): void
    {
        $client = AbstractTest::getClient();
        //$client->followRedirects();
        $this->doAuth($client, "user@user.com", "pass_123456");
        $client->followRedirect();
        $client->request('GET', '/course/');
        $em = AbstractTest::getEntityManager();
        $courses = $em->getRepository(Course::class)->findAll();
        $countCourse = count($courses);
        $this->assertEquals(3, $countCourse);

        $this->assertResponseOk();
    }

    public function testNewGet(): void
    {
        $client = AbstractTest::getClient();
        $this->doAuth($client, "admin@admin.com", "pass_123456");
        $client->followRedirect();
        $client->request('GET', '/course/new');

        $this->assertResponseOk();
    }

    public function testEditGet(): void
    {
        $client = AbstractTest::getClient();
        $this->doAuth($client, "admin@admin.com", "pass_123456");
        $client->followRedirect();
        $em = $this->getEntityManager();
        $courses = $em->getRepository(Course::class)->findAll();

        foreach ($courses as $course) {
            $idCourse = $course->getID();
            $client->request('GET', '/course/edit/' . $idCourse);
            $this->assertResponseOk();
        }
    }

    public function testShowGet(): void
    {
        $client = AbstractTest::getClient();
        $this->doAuth($client, "user@user.com", "pass_123456");
        $client->followRedirect();
        $em = $this->getEntityManager();
        $courses = $em->getRepository(Course::class)->findAll();

        foreach ($courses as $course) {
            $idCourse = $course->getID();
            $client->request('GET', '/course/' . $idCourse);

            $lessons = $em->getRepository(Lesson::class)->findBy(['course' => $idCourse]);

            $this->assertEquals(3, count($lessons));
            $this->assertResponseOk();
        }
    }

    public function testNotFound(): void
    {
        $client = AbstractTest::getClient();
        $client->request('GET', "/notfound/");

        $this->assertResponseNotFound();

    }

    public function testFormNewError(): void
    {
        $client = AbstractTest::getClient();
        $this->doAuth($client, "admin@admin.com", "pass_123456");
        $client->followRedirect();
        $url = '/course/';
        $crawler = $client->request('GET', $url);
        $this->assertResponseOk();

        $link = $crawler->selectLink('Создать курс')->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();

        $em = $this->getEntityManager();
        $coursesCountBefore = count($em->getRepository(Course::class)->findAll());
        $form = $crawler->filter('form')->form();

        $form->setValues(array(
            "course[name]" => "N",
            "course[description]"  => "N",
            "course[code]" => "2",
        ));

        $crawler = $client->submit($form);

        $errorMassageExpect = [
            'name' => 'Имя должно быть больше 4 символов',
            'description' => 'Контент должен быть больше 10 символов',
            'code' => 'Код должен быть больше 2 символов',
        ];

        $errorMassageActual = [
            'name' => $crawler->filter('li')->eq(1)->text(),
            'description' => $crawler->filter('li')->eq(2)->text(),
            'code' => $crawler->filter('li')->eq(3)->text(),
        ];

        $this->assertError($errorMassageExpect, $errorMassageActual);

        $form->setValues(array(
            "course[code]" => "111",
        ));

        $crawler = $client->submit($form);
        $errorMassageExpect = [
            'code' => 'Курс с таким кодом уже существует',
        ];

        $errorMassageActual = [
            'code' => $crawler->filter('li')->eq(3)->text(),
        ];

        $this->assertError($errorMassageExpect, $errorMassageActual);

        $coursesCountAfter = count($em->getRepository(Course::class)->findAll());
        $this->assertEquals($coursesCountBefore, $coursesCountAfter);
    }

    public function testFormNewOk(): void
    {
        $client = AbstractTest::getClient();
        $this->doAuth($client, "admin@admin.com", "pass_123456");
        $client->followRedirect();
        $url = '/course/';
        $crawler = $client->request('GET', $url);
        $this->assertResponseOk();

        $urlExpected = $crawler->getUri();

        $link = $crawler->selectLink('Создать курс')->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();

        $em = $this->getEntityManager();
        $coursesCountBefore = count($em->getRepository(Course::class)->findAll());
        $form = $crawler->filter('form')->form();

        $form->setValues(array(
            "course[name]" => "Name",
            "course[description]"  => "Description test",
            "course[code]" => "22443334343332e4",
        ));

        $client->submit($form);
        $this->assertResponseRedirect();

        $crawler = $client->followRedirect();
        $this->assertSame($urlExpected, $crawler->getUri());

        $coursesCountAfter = count($em->getRepository(Course::class)->findAll());
        $this->assertEquals($coursesCountBefore + 1, $coursesCountAfter);
    }

    public function testCourseDelete(): void
    {
        $client = AbstractTest::getClient();
        $this->doAuth($client, "admin@admin.com", "pass_123456");
        $client->followRedirect();
        $url = '/course/';
        $crawler = $client->request('GET', $url);
        $this->assertResponseOk();
        $urlExpected = $crawler->getUri();
        $link = $crawler->selectLink('Учить')->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();

        $em = $this->getEntityManager();
        $coursesCountBefore = count($em->getRepository(Course::class)->findAll());

        $form = $crawler->filter('form')->form();
        $client->submit($form);
        $this->assertResponseRedirect();
        $crawler = $client->followRedirect();

        $this->assertSame($urlExpected, $crawler->getUri());

        $coursesCountAfter = count($em->getRepository(Course::class)->findAll());
        $this->assertEquals($coursesCountBefore - 1, $coursesCountAfter);
    }

    public function testFormEditOk(): void
    {
        $client = AbstractTest::getClient();
        $this->doAuth($client, "admin@admin.com", "pass_123456");
        $client->followRedirect();
        $url = '/course/';
        $crawler = $client->request('GET', $url);
        $this->assertResponseOk();
        $link = $crawler->selectLink('Учить')->link();
        $crawler = $client->click($link);
        $url = $crawler->getUri();
        $segments = explode('/', $url);
        $idCourse = $segments[4];
        $this->assertResponseOk();
        $urlExpected = $crawler->getUri();
        $link = $crawler->selectLink('Edit')->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();

        $form = $crawler->filter('form')->form();

        $form->setValues(array(
            "course[name]" => "New name",
            "course[description]"  => "New description",
            "course[code]" => "New code",
        ));

        $client->submit($form);
        $this->assertResponseRedirect();
        $crawler = $client->followRedirect();

        $this->assertSame($urlExpected, $crawler->getUri());

        $em = $this->getEntityManager();
        $course = $em->getRepository(Course::class)->find($idCourse);

        $this->assertSame("New name", $course->getName());
        $this->assertSame("New description", $course->getDescription());
        $this->assertSame("New code", $course->getCode());
    }

    public function testFormEditError(): void
    {
        $client = AbstractTest::getClient();
        $this->doAuth($client, "admin@admin.com", "pass_123456");
        $client->followRedirect();
        $url = '/course/';
        $crawler = $client->request('GET', $url);
        $this->assertResponseOk();

        $link = $crawler->selectLink('Учить')->link();
        $crawler = $client->click($link);
        $url = $crawler->getUri();
        $segments = explode('/', $url);
        $idCourse = $segments[4];
        $this->assertResponseOk();

        $em = $this->getEntityManager();

        $link = $crawler->selectLink('Edit')->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();

        $form = $crawler->filter('form')->form();

        $form->setValues(array(
            "course[name]" => "N",
            "course[description]"  => "n",
            "course[code]" => "n",
        ));

        $crawler = $client->submit($form);

        $errorMassageExpect = [
            'name' => 'Имя должно быть больше 4 символов',
            'description' => 'Контент должен быть больше 10 символов',
            'code' => 'Код должен быть больше 2 символов'
        ];

        $errorMassageActual = [
            'name' => $crawler->filter('li')->eq(1)->text(),
            'description' => $crawler->filter('li')->eq(2)->text(),
            'code' => $crawler->filter('li')->eq(3)->text(),
        ];
        $this->assertError($errorMassageExpect, $errorMassageActual);

        $form->setValues(array(
            "course[code]" => "222",
        ));
        $crawler = $client->submit($form);

        $errorMassageExpect = [
            'code' => 'Курс с таким кодом уже существует',
        ];

        $errorMassageActual = [
            'code' => $crawler->filter('li')->eq(3)->text(),
        ];
        $this->assertError($errorMassageExpect, $errorMassageActual);

        $client->request('GET', $url);
        $this->assertResponseOk();


        $course = $em->getRepository(Course::class)->find($idCourse);




        $this->assertNotSame("N", $course->getName());
        $this->assertNotSame("n", $course->getDescription());
        $this->assertNotSame("n", $course->getCode());
    }
}
