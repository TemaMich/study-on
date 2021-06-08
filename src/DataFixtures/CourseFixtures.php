<?php

namespace App\DataFixtures;

use App\Entity\Course;
use App\Entity\Lesson;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CourseFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $arrayCourse = [
            'courseName' => [
                'Изучение верстки',
                'Основы PHP за 24 часа',
                'Symfony: От новичка до мастера',
            ],
            'courseDescription' => [
                'Обучение основам HTML и CSS. А так же научим верстать ваше портфолио',
                'Обучим самым осовным фещам в php. Изучим циклы, массивы, функции, а так же затронем ООП',
                'Обучим всему необходимому, чтобы написать свой первый сайт на symfony',
            ],
            'courseCode' => [
                '111',
                '222',
                '333',
            ],
        ];

        $arrayLesson = [
            [
                'lessonName' => [
                    'Основы HTML',
                    'Основы CSS',
                    'Пишем совой сайт портфолио',
                ],
                'lessonContent' => [
                    'Мы изучим тег div, a, table, span, hr... Мы все изучили, удачи в следующем уроке ',
                    'Сегодня мы изучаем основные стили background, color... Мы изучили основные стили, переходим к следующему уроку',
                    'Теперь мы всезнаем, осталось только написать сайт, делать мы будем это так... Молодцы, теперь у вас есть свой сайт',
                ],
            ],
            [
                'lessonName' => [
                    'Изучение циклов',
                    'Изучение массивов',
                    'Функции и чутка ООП',
                ],
                'lessonContent' => [
                    'Циклы работают таким образом... Вот мы и изучили циклы',
                    'Массивы это очень важная тема и они часно используются вместе с циклами. Вот как они работают... Вот мы и изучили массивы',
                    'Функции сложная тема и они работают вот так... Вот мы и изучили функциии. И сейчас немного про ООП...',
                ],
            ],
            [
                'lessonName' => [
                    'Зачем нужен фреймворк',
                    'Сущности, контроллеры, представления',
                    'Простенький сайт на симфони',
                ],
                'lessonContent' => [
                    'Фреймворк ускоряет работу над сайтом, и упрощает поддержку в будующем, а самое главное... Теперь мы знаем зачем нужен фреймворк.',
                    'Сейчас мы изучим сущности, контроллеры и представления... Отлично, теперь мы сможем написать первый сайт',
                    'Сейчас я расскажу как написать сайт, чтобы он не лагал и работал всегда прекрасно... Отлично, теперь вы знаете как написать сайт',
                ],
            ],
        ];

        $course = [];
        $lesson = [];

        for ($i = 0; $i < 3; $i++) {
            $course[$i] = new Course();
        }

        foreach ($course as $courseKey => $courseItem) {
            $courseItem->setName($arrayCourse['courseName'][$courseKey]);
            $courseItem->setDescription($arrayCourse['courseDescription'][$courseKey]);
            $courseItem->setCode($arrayCourse['courseCode'][$courseKey]);

            for ($i = 0; $i < 3; $i++) {
                $lesson[$i] = new Lesson();
            }

            foreach ($lesson as $lessonKey => $lessonItem) {
                $lessonItem->setName($arrayLesson[$courseKey]['lessonName'][$lessonKey]);
                $lessonItem->setContent($arrayLesson[$courseKey]['lessonContent'][$lessonKey]);
                $lessonItem->setNumber($lessonKey + 1);
                $lessonItem->setCourse($courseItem);
                $manager->persist($lessonItem);
            }
            $manager->persist($courseItem);
        }
        $manager->flush();
    }
}
