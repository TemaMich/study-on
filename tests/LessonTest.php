<?php

namespace App\Tests;

use App\Controller\CourseController;
use App\DataFixtures\CourseFixtures;
use App\Entity\Course;
use App\Entity\Lesson;
use App\Repository\CourseRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LessonTest extends AbstractTest
{
    protected function getFixtures(): array
    {
        return [CourseFixtures::class];
    }

    public function testShow(): void
    {
        $client = AbstractTest::getClient();
        $em = $this->getEntityManager();
        $lessons = $em->getRepository(Lesson::class)->findAll();
        foreach ($lessons as $lesson) {
            $id = $lesson->getID();
            $crawler = $client->request('GET', 'http://study-on.local:81/lesson/' . $id);
            $this->assertResponseOk();
        }
    }

    public function testNew(): void
    {
        $client = AbstractTest::getClient();
        $em = $this->getEntityManager();
        $courses = $em->getRepository(Course::class)->findAll();
        foreach ($courses as $cours) {
            $id = $cours->getID();
            $crawler = $client->request('GET', 'http://study-on.local:81/lesson/new/' . $id);
            $this->assertResponseOk();
        }
    }

    public function testEdit(): void
    {
        $client = AbstractTest::getClient();
        $em = $this->getEntityManager();
        $lessons = $em->getRepository(Lesson::class)->findAll();
        foreach ($lessons as $lesson) {
            $id = $lesson->getID();
            $crawler = $client->request('GET', 'http://study-on.local:81/lesson/' . $id . '/edit');
            $this->assertResponseOk();
        }
    }

    public function testNotFound(): void
    {
        $client = AbstractTest::getClient();
        $crawler = $client->request('GET', 'http://study-on.local:81/lessonnsaxa');
        $this->assertResponseNotFound();
    }

    public function testFormNewOk(): void
    {
        $client = AbstractTest::getClient();
        $url = 'http://study-on.local:81/course/';
        $crawler = $client->request('GET', $url);
        $this->assertResponseOk();
        $link = $crawler->selectLink('Учить')->link();
        $crawler = $client->click($link);
        $uri = $crawler->getUri();
        $segments = explode('/', $uri);
        $id = $segments[4];
        $this->assertResponseOk();
        $em = $this->getEntityManager();
        $lessonsCountBefore = count($em->getRepository(Lesson::class)->findBy(['course' => $id]));
        $link = $crawler->selectLink('New')->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();
        $form = $crawler->filter('form')->form();

        $form->setValues(array(
            "lesson[name]" => "New name",
            "lesson[content]"  => "New content",
            "lesson[number]" => "1",
        ));

        $crawler = $client->submit($form);
        $this->assertResponseRedirect();
        $crawler = $client->followRedirect();
        $this->assertSame('http://study-on.local:81/course/' . $id, $crawler->getUri());

        $lessonCountAfter = count($em->getRepository(Lesson::class)->findBy(['course' => $id]));
        $this->assertEquals($lessonsCountBefore + 1, $lessonCountAfter);

        $lessons = $em->getRepository(Lesson::class)->findBy(['course' => $id], ['id' => 'DESC'], 1);
        $lesson = array_shift($lessons);
        $this->assertSame("New name", $lesson->getName());
        $this->assertSame("New content", $lesson->getContent());
        $this->assertEquals(1, $lesson->getNumber());
    }

    public function testFormNewError(): void
    {
        $client = AbstractTest::getClient();
        $url = 'http://study-on.local:81/course/';
        $crawler = $client->request('GET', $url);
        $this->assertResponseOk();
        $link = $crawler->selectLink('Учить')->link();
        $crawler = $client->click($link);
        $uri = $crawler->getUri();
        $segments = explode('/', $uri);
        $id = $segments[4];
        $this->assertResponseOk();
        $em = $this->getEntityManager();
        $lessonsCountBefore = count($em->getRepository(Lesson::class)->findBy(['course' => $id]));
        $link = $crawler->selectLink('New')->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();
        $form = $crawler->filter('form')->form();

        $form->setValues(array(
            "lesson[name]" => "N",
            "lesson[content]"  => "N",
            "lesson[number]" => "N",
        ));

        $crawler = $client->submit($form);

        $errorMassageExpect = [
            'name' => 'Название должно быть больше 5 символов',
            'content' => 'Контент должен быть больше 10 символов',
            'number' => 'This value is not valid.',
        ];

        $errorMassageActual = [
            'name' => $crawler->filter('li')->eq(0)->text(),
            'content' => $crawler->filter('li')->eq(1)->text(),
            'number' => $crawler->filter('li')->eq(2)->text(),
        ];
        $this->assertError($errorMassageExpect, $errorMassageActual);

        $lessonCountAfter = count($em->getRepository(Lesson::class)->findBy(['course' => $id]));
        $this->assertEquals($lessonsCountBefore, $lessonCountAfter);
    }

    public function testLessonDelete(): void
    {
        $client = AbstractTest::getClient();
        $url = 'http://study-on.local:81/course/';
        $crawler = $client->request('GET', $url);
        $this->assertResponseOk();
        $link = $crawler->selectLink('Учить')->link();
        $crawler = $client->click($link);
        $uri = $crawler->getUri();
        $segments = explode('/', $uri);
        $id = $segments[4];
        $this->assertResponseOk();
        $em = $this->getEntityManager();
        $lessonsCountBefore = count($em->getRepository(Lesson::class)->findBy(['course' => $id]));
        $this->assertResponseOk();
        $link = $crawler->selectLink('Основы HTML')->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();
        $form = $crawler->filter('form')->form();

        $crawler = $client->submit($form);
        $this->assertResponseRedirect();
        $crawler = $client->followRedirect();
        $this->assertSame('http://study-on.local:81/course/' . $id, $crawler->getUri());
        $lessonCountAfter = count($em->getRepository(Lesson::class)->findBy(['course' => $id]));
        $this->assertEquals($lessonsCountBefore - 1, $lessonCountAfter);
    }

    public function testFormEditOk(): void
    {
        $client = AbstractTest::getClient();
        $url = 'http://study-on.local:81/course/';
        $crawler = $client->request('GET', $url);
        $this->assertResponseOk();
        $link = $crawler->selectLink('Учить')->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();
        $em = $this->getEntityManager();
        $this->assertResponseOk();
        $link = $crawler->selectLink('Основы HTML')->link();
        $crawler = $client->click($link);
        $uri = $crawler->getUri();
        $segments = explode('/', $uri);
        $id = $segments[4];
        $lessonsCountBefore = count($em->getRepository(Lesson::class)->findBy(['course' => $id]));
        $this->assertResponseOk();
        $link = $crawler->selectLink('Edit')->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();
        $form = $crawler->filter('form')->form();

        $form->setValues(array(
            "lesson[name]" => "New name",
            "lesson[content]"  => "New content",
        ));

        $crawler = $client->submit($form);
        $this->assertResponseRedirect();
        $crawler = $client->followRedirect();
        $this->assertSame('http://study-on.local:81/lesson/' . $id, $crawler->getUri());

        $em = $this->getEntityManager();
        $lesson = $em->getRepository(Lesson::class)->find($id);
        $this->assertSame("New name", $lesson->getName());
        $this->assertSame("New content", $lesson->getContent());
    }

    public function testFormEditError(): void
    {
        $client = AbstractTest::getClient();
        $url = 'http://study-on.local:81/course/';
        $crawler = $client->request('GET', $url);
        $this->assertResponseOk();
        $link = $crawler->selectLink('Учить')->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();
        $em = $this->getEntityManager();
        $this->assertResponseOk();
        $link = $crawler->selectLink('Основы HTML')->link();
        $crawler = $client->click($link);
        $uri = $crawler->getUri();
        $segments = explode('/', $uri);
        $id = $segments[4];
        $lessonsCountBefore = count($em->getRepository(Lesson::class)->findBy(['course' => $id]));
        $this->assertResponseOk();
        $link = $crawler->selectLink('Edit')->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();
        $form = $crawler->filter('form')->form();

        $form->setValues(array(
            "lesson[name]" => "N",
            "lesson[content]"  => "N",
            "lesson[number]" => "N",
        ));

        $crawler = $client->submit($form);

        $errorMassageExpect = [
            'name' => 'Название должно быть больше 5 символов',
            'content' => 'Контент должен быть больше 10 символов',
            'number' => 'This value is not valid.',
        ];

        $errorMassageActual = [
            'name' => $crawler->filter('li')->eq(0)->text(),
            'content' => $crawler->filter('li')->eq(1)->text(),
            'number' => $crawler->filter('li')->eq(2)->text(),
        ];
        $this->assertError($errorMassageExpect, $errorMassageActual);
        $lesson = $em->getRepository(Lesson::class)->find($id);
        $this->assertNotSame("N", $lesson->getName());
        $this->assertNotSame("N", $lesson->getContent());
        $this->assertNotSame("N", $lesson->getNumber());
    }
}
