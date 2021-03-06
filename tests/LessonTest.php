<?php

namespace App\Tests;

use App\DataFixtures\CourseFixtures;
use App\Entity\Course;
use App\Entity\Lesson;

class LessonTest extends AbstractTest
{
    protected function getFixtures(): array
    {
        return [CourseFixtures::class];
    }

    public function testShowGet(): void
    {
        $client = AbstractTest::getClient();
        $this->doAuth($client, "user@user.com", "pass_123456");
        $client->followRedirect();
        $em = $this->getEntityManager();
        $lessons = $em->getRepository(Lesson::class)->findAll();

        foreach ($lessons as $lesson) {
            $idLesson = $lesson->getID();
            $client->request('GET', '/lesson/' . $idLesson);
            $this->assertResponseOk();
        }
    }

    public function testNewGet(): void
    {
        $client = AbstractTest::getClient();
        $this->doAuth($client, "admin@admin.com", "pass_123456");
        $client->followRedirect();
        $em = $this->getEntityManager();
        $courses = $em->getRepository(Course::class)->findAll();

        foreach ($courses as $course) {
            $idLesson = $course->getID();
            $client->request('GET', '/lesson/new/' . $idLesson);
            $this->assertResponseOk();
        }
    }

    public function testEditGet(): void
    {
        $client = AbstractTest::getClient();
        $this->doAuth($client, "admin@admin.com", "pass_123456");
        $client->followRedirect();
        $em = $this->getEntityManager();
        $lessons = $em->getRepository(Lesson::class)->findAll();

        foreach ($lessons as $lesson) {
            $idLesson = $lesson->getID();
            $client->request('GET', '/lesson/edit/' . $idLesson);
            $this->assertResponseOk();
        }
    }

    public function testNotFound(): void
    {
        $client = AbstractTest::getClient();

        $client->request('GET', '/notfound');
        $this->assertResponseNotFound();
    }

    public function testFormNewOk(): void
    {
        $client = AbstractTest::getClient();
        $this->doAuth($client, "admin@admin.com", "pass_123456");
        $client->followRedirect();
        $url = '/course/';
        $crawler = $client->request('GET', $url);
        $this->assertResponseOk();
        $link = $crawler->selectLink('??????????')->link();
        $crawler = $client->click($link);
        $url = $crawler->getUri();
        $segments = explode('/', $url);
        $idCourse = $segments[4];
        $this->assertResponseOk();
        $urlExpected = $crawler->getUri();
        $em = $this->getEntityManager();
        $lessonsCountBefore = count($em->getRepository(Lesson::class)->findBy(['course' => $idCourse]));

        $link = $crawler->selectLink('New')->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();

        $form = $crawler->filter('form')->form();

        $form->setValues(array(
            "lesson[name]" => "New name",
            "lesson[content]"  => "New content",
            "lesson[number]" => "1",
        ));

        $client->submit($form);
        $this->assertResponseRedirect();
        $crawler = $client->followRedirect();

        $this->assertSame($urlExpected, $crawler->getUri());

        $lessonCountAfter = count($em->getRepository(Lesson::class)->findBy(['course' => $idCourse]));
        $this->assertEquals($lessonsCountBefore + 1, $lessonCountAfter);

        $lessons = $em->getRepository(Lesson::class)->findBy(['course' => $idCourse], ['id' => 'DESC'], 1);
        $lesson = array_shift($lessons);

        $this->assertSame("New name", $lesson->getName());
        $this->assertSame("New content", $lesson->getContent());
        $this->assertEquals(1, $lesson->getNumber());
    }

    public function testFormNewError(): void
    {
        $client = AbstractTest::getClient();
        $this->doAuth($client, "admin@admin.com", "pass_123456");
        $client->followRedirect();
        $url = '/course/';
        $crawler = $client->request('GET', $url);
        $this->assertResponseOk();

        $link = $crawler->selectLink('??????????')->link();
        $crawler = $client->click($link);
        $url = $crawler->getUri();
        $segments = explode('/', $url);
        $idCourse = $segments[4];
        $this->assertResponseOk();

        $em = $this->getEntityManager();
        $lessonsCountBefore = count($em->getRepository(Lesson::class)->findBy(['course' => $idCourse]));

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
            'name' => '???????????????? ???????????? ???????? ???????????? 5 ????????????????',
            'content' => '?????????????? ???????????? ???????? ???????????? 10 ????????????????',
            'number' => 'This value is not valid.',
        ];

        $errorMassageActual = [
            'name' => $crawler->filter('li')->eq(1)->text(),
            'content' => $crawler->filter('li')->eq(2)->text(),
            'number' => $crawler->filter('li')->eq(3)->text(),
        ];
        $this->assertError($errorMassageExpect, $errorMassageActual);

        $lessonCountAfter = count($em->getRepository(Lesson::class)->findBy(['course' => $idCourse]));
        $this->assertEquals($lessonsCountBefore, $lessonCountAfter);
    }

    public function testLessonDelete(): void
    {
        $client = AbstractTest::getClient();
        $this->doAuth($client, "admin@admin.com", "pass_123456");
        $client->followRedirect();
        $url = '/course/';
        $crawler = $client->request('GET', $url);
        $this->assertResponseOk();
        $link = $crawler->selectLink('??????????')->link();
        $crawler = $client->click($link);
        $url = $crawler->getUri();
        $segments = explode('/', $url);
        $idCourse = $segments[4];
        $this->assertResponseOk();
        $urlExpected = $crawler->getUri();
        $em = $this->getEntityManager();
        $lessonsCountBefore = count($em->getRepository(Lesson::class)->findBy(['course' => $idCourse]));
        $this->assertResponseOk();

        $link = $crawler->selectLink('???????????? HTML')->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();

        $form = $crawler->filter('form')->form();
        $client->submit($form);
        $this->assertResponseRedirect();
        $crawler = $client->followRedirect();

        $this->assertSame($urlExpected, $crawler->getUri());

        $lessonCountAfter = count($em->getRepository(Lesson::class)->findBy(['course' => $idCourse]));
        $this->assertEquals($lessonsCountBefore - 1, $lessonCountAfter);
    }

    public function testFormEditOk(): void
    {
        $client = AbstractTest::getClient();
        $this->doAuth($client, "admin@admin.com", "pass_123456");
        $client->followRedirect();
        $url = '/course/';
        $crawler = $client->request('GET', $url);
        $this->assertResponseOk();

        $link = $crawler->selectLink('??????????')->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();

        $this->assertResponseOk();
        $link = $crawler->selectLink('???????????? HTML')->link();
        $crawler = $client->click($link);
        $uri = $crawler->getUri();
        $segments = explode('/', $uri);
        $idCourse = $segments[4];
        $this->assertResponseOk();
        $urlExpected = $crawler->getUri();
        $link = $crawler->selectLink('Edit')->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();

        $form = $crawler->filter('form')->form();

        $form->setValues(array(
            "lesson[name]" => "New name",
            "lesson[content]"  => "New content",
        ));

        $client->submit($form);
        $this->assertResponseRedirect();
        $crawler = $client->followRedirect();

        $this->assertSame($urlExpected, $crawler->getUri());

        $em = $this->getEntityManager();
        $lesson = $em->getRepository(Lesson::class)->find($idCourse);

        $this->assertSame("New name", $lesson->getName());
        $this->assertSame("New content", $lesson->getContent());
    }

    public function testFormEditError(): void
    {
        $client = AbstractTest::getClient();
        $this->doAuth($client, "admin@admin.com", "pass_123456");
        $client->followRedirect();
        $url = '/course/';
        $crawler = $client->request('GET', $url);
        $this->assertResponseOk();

        $link = $crawler->selectLink('??????????')->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();

        $link = $crawler->selectLink('???????????? HTML')->link();
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
            "lesson[name]" => "N",
            "lesson[content]"  => "N",
            "lesson[number]" => "N",
        ));

        $crawler = $client->submit($form);

        $errorMassageExpect = [
            'name' => '???????????????? ???????????? ???????? ???????????? 5 ????????????????',
            'content' => '?????????????? ???????????? ???????? ???????????? 10 ????????????????',
            'number' => 'This value is not valid.',
        ];

        $errorMassageActual = [
            'name' => $crawler->filter('li')->eq(1)->text(),
            'content' => $crawler->filter('li')->eq(2)->text(),
            'number' => $crawler->filter('li')->eq(3)->text(),
        ];
        $this->assertError($errorMassageExpect, $errorMassageActual);

        $crawler = $client->request('GET', $url);
        $this->assertResponseOk();


        $lesson = $em->getRepository(Lesson::class)->find($idCourse);

        $this->assertNotSame("N", $lesson->getName());
        $this->assertNotSame("N", $lesson->getContent());
        $this->assertNotSame("N", $lesson->getNumber());
    }
}
