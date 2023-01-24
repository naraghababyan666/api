<?php

namespace Database\Seeders;

use App\Models\BasketList;
use App\Models\Category;
use App\Models\CategoryTranslation;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\Section;
use App\Models\SectionLesson;
use App\Models\Trainer;
use App\Models\User;
use App\Models\WishList;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if(env('DEVELOPMENT_MODE')) {
            User::firstOrCreate([
                "id" => 999,
                "first_name" => "Admin",
                "last_name" => "Admin",
                "email" => "admin777@mail.ru",
                "password" => Hash::make("brainfors2017"),
                "role_id" => 1,
                "company_name" => "Upstart1",
                "tax_identity_number" => 0
            ]);
            User::firstOrCreate([
                "id" => 1000,
                "first_name" => "Upstart",
                "last_name" => "Trainer1",
                "email" => "upstarttest@mail.ru",
                "password" => Hash::make("brainfors2017"),
                "role_id" => 3,
                "company_name" => "Upstart1",
                "tax_identity_number" => 0
            ]);
            Trainer::firstOrCreate([
                'id' => 1000,
                'first_name' => 'Upstart',
                'last_name' => 'Trainer1',
                'user_id' => 1000
            ]);
            User::firstOrCreate([
                "id" => 1001,
                "first_name" => "Upstart",
                "last_name" => "Trainer2",
                "email" => "testupstart2017@gmail.com",
                "password" => Hash::make("brainfors2017"),
                "role_id" => 3,
                "company_name" => "Upstart2",
                "tax_identity_number" => 0
            ]);
            Trainer::firstOrCreate([
                'id' => 1001,
                'first_name' => 'Upsstart',
                'last_name' => 'Trainer2',
                'user_id' => 1001
            ]);

            User::firstOrCreate([
                "id" => 1002,
                "first_name" => "Student1",
                "last_name" => "Student1",
                "email" => "student1@mail.ru",
                "password" => Hash::make("brainfors2017"),
                "role_id" => 5,
                "company_name" => "Upstart",
                "tax_identity_number" => 0
            ]);
            User::firstOrCreate([
                "id" => 1003,
                "first_name" => "Student2",
                "last_name" => "Student2",
                "email" => "student2@mail.ru",
                "password" => Hash::make("brainfors2017"),
                "role_id" => 5,
                "company_name" => "Upstart",
                "tax_identity_number" => 0
            ]);
            User::firstOrCreate([
                "id" => 1004,
                "first_name" => "Student3",
                "last_name" => "Student3",
                "email" => "student3@mail.ru",
                "password" => Hash::make("brainfors2017"),
                "role_id" => 5,
                "company_name" => "Upstart",
                "tax_identity_number" => 0
            ]);

            Category::updateOrCreate([
                'id' => 1000,
                'parent_id' => null,
                'ordering' => 1
            ],);
            Category::updateOrCreate([
                'id' => 1001,
                'parent_id' => 1,
                'ordering' => 1
            ]);
            Category::updateOrCreate([
                'id' => 1002,
                'parent_id' => 1,
                'ordering' => 2
            ]);

            CategoryTranslation::updateOrCreate([
                'id' => 1000,
                'title' => 'Development',
                'category_id' => 1001,
                'language_id' => 2
            ]);
            CategoryTranslation::updateOrCreate([
                'id' => 1001,
                'title' => 'Development',
                'category_id' => 1002,
                'language_id' => 2
            ]);

            Course::firstOrCreate([
                'id' => 1000,
                'user_id' => 999,
                'cover_image' => 'images/course/laravel.png',
                'promo_video' => 'videos/course/intro.mp4',
                'title' => 'PHP with Laravel for beginners - Become a Master in Laravel',
                'description' => "Right now on Laravel 7.10 but of course as new versions come out, I will keep updating the course.Over 30,000 students in this course and over 600,000 students                       here at Udemy.Best Rated, Best Selling, Biggest and just baddest course on Laravel around :)
                                        Oh, it's also the best course for complete beginners and of course regular beginners :)
                                        Laravel has become one of the most popular if not the most popular PHP framework. Employers are asking for this skill for all web programming jobs and in this course we have put together all of them, to give you the best chance of landing that job; or taking it to the next level.
                                        Why is Laravel so popular? Because once you learn it, creating complex applications are easy to do, because thousands of other people have created code we can plug (packages) into our Laravel application to make it even better.
                                        There are many reasons why Laravel is on the top when it comes to PHP frameworks but we are not here to talk about that, right?
                                        You are here because you want to learn Laravel, and find out what course to take, right? Alright, let's list what this course has to offer so that you can make your decision?
                                        Benefits of taking this course (I promise to be  brief)
                                        1. Top PHP instructor (with other successful PHP courses with great reviews)
                                        2. Top support groups
                                        3. An amazing project that we will be building and taking to Github
                                        4. Lots of cybernetic coffee to keep you awake.....
                                        5. Did I mention I was not boring and you will not fall asleep?",
                'sub_title' => 'Learn to master Laravel to make advanced applications like the real CMS app we build on this course',
                'language' => 2,
                'type' => 1,
                'status' => 3,
                'category_id' => 1,
                'max_participants' => 15,
                'level' => 2,
                'trainer_id' => 1001,
                'price' => 12.99,
                'currency' => 'USD'
            ]);
            Course::firstOrCreate([
                'id' => 1001,
                'user_id' => 1000,
                'cover_image' => 'images/course/laravel.png',
                'promo_video' => 'videos/course/intro.mp4',
                'title' => 'PHP for Beginners - Become a PHP Master - CMS Project',
                'description' => "Right now on Laravel 7.10 but of course as new versions come out, I will keep updating the course.Over 30,000 students in this course and over 600,000 students                       here at Udemy.Best Rated, Best Selling, Biggest and just baddest course on Laravel around :)
                                            Oh, it's also the best course for complete beginners and of course regular beginners :)
                                            Laravel has become one of the most popular if not the most popular PHP framework. Employers are asking for this skill for all web programming jobs and in this course we have put together all of them, to give you the best chance of landing that job; or taking it to the next level.
                                            Why is Laravel so popular? Because once you learn it, creating complex applications are easy to do, because thousands of other people have created code we can plug (packages) into our Laravel application to make it even better.
                                            There are many reasons why Laravel is on the top when it comes to PHP frameworks but we are not here to talk about that, right?
                                            You are here because you want to learn Laravel, and find out what course to take, right? Alright, let's list what this course has to offer so that you can make your decision?
                                            Benefits of taking this course (I promise to be  brief)
                                            1. Top PHP instructor (with other successful PHP courses with great reviews)
                                            2. Top support groups
                                            3. An amazing project that we will be building and taking to Github
                                            4. Lots of cybernetic coffee to keep you awake.....
                                            5. Did I mention I was not boring and you will not fall asleep?", 'sub_title' => 'PHP for Beginners: learn everything you need to become a professional PHP developer with practical exercises & projects.',
                'language' => 1,
                'type' => 1,
                'status' => 3,
                'category_id' => 2,
                'max_participants' => 20,
                'level' => 1,
                'trainer_id' => 1000,
                'price' => 8122.0,
                'currency' => 'AMD',
            ]);
            Course::firstOrCreate([
                'id' => 1002,
                'user_id' => 999,
                'cover_image' => 'images/course/laravel.png',
                'promo_video' => 'videos/course/intro.mp4',
                'title' => 'APIs in PHP: from Basic to Advanced',
                'description' => "An API is a way for a program to interact with another program. By using third-party APIs from your code, you can utilise functionality developed elsewhere. By                    creating an API to access your own data, other programs can take advantage of your services in a secure and easy fashion.
                                Learn how to Use and Create Secure and Scalable APIs in PHP in this Comprehensive Course.
                                Understand how APIs work
                                Learn how to use an API from PHP
                                Understand how HTTP requests and responses work
                                Understand what REST and RESTful APIs are
                                Create a RESTful API from scratch, using plain PHP and MySQL
                                Understand how API authentication works
                                Add API key authentication to your API
                                Understand how JSON Web Tokens (JWTs) work
                                Add JWT access token authentication to your API
                                The essential skills required to use and develop APIs with PHP.",
                'sub_title' => 'Use REST APIs from PHP, and create your own RESTful API using plain PHP, with API key and JWT token authentication',
                'language' => 2,
                'type' => 3,
                'status' => 3,
                'category_id' => 1001,
                'max_participants' => 20,
                'level' => 2,
                'trainer_id' => 1001,
                'price' => 15000.0,
                'currency' => 'AMD',
            ]);
            Course::firstOrCreate([
                'id' => 1003,
                'user_id' => 999,
                'cover_image' => 'images/course/react.png',
                'promo_video' => 'videos/course/intro.mp4',
                'title' => 'React - The Complete Guide (incl Hooks, React Router, Redux)',
                'description' => "This course is fully up-to-date with React 18 (the latest version of React)!
                                  It was completely updated and re-recorded from the ground up - it teaches the very latest version of React with all the core, modern features you need to know!",
                'sub_title' => 'Dive in and learn React.js from scratch! Learn Reactjs, Hooks, Redux, React Routing, Animations, Next.js and way more!',
                'language' => 2,
                'type' => 2,
                'status' => 3,
                'category_id' => 1002,
                'max_participants' => 15,
                'level' => 1,
                'trainer_id' => 1000,
                'price' => 12.99,
                'currency' => 'USD',
            ]);
            Course::firstOrCreate([
                'id' => 1004,
                'user_id' => 999,
                'cover_image' => 'images/course/react.png',
                'promo_video' => 'videos/course/intro.mp4',
                'title' => 'React Tutorial and Projects Course (2022)',
                'description' => "React was released by Facebook's web development team in 2013 as a view library. React is one of the best choices for building modern web applications. React has a                   slim API, a robust and evolving ecosystem and a great community. In this course we will be learning React by creating various projects.If you want to learn more                    than just same old tutorial and instead create interesting projects using React.js this course is for you. During the course we will also cover redux-toolkit,                      which is the latest flavor of good old redux and build an interesting project with it. After each tutorial section we will build few projects to put see theory                     in action. You are not expected to build all projects, but the more course projects, you will complete the easier it's going to be to build your own                                applications, since you will know how to implement certain features in react.",
                'sub_title' => 'Learn React by Building 25+ Interesting Projects',
                'language' => 2,
                'type' => 1,
                'status' => 3,
                'category_id' => 1002,
                'max_participants' => 17,
                'level' => 3,
                'trainer_id' => 1000,
                'price' => 19.99,
                'currency' => 'USD',
            ]);
            Course::firstOrCreate([
                'id' => 1005,
                'user_id' => 999,
                'cover_image' => 'images/course/react.png',
                'promo_video' => 'videos/course/intro.mp4',
                'title' => 'Complete React Developer in 2023 (w/ Redux, Hooks, GraphQL)',
                'description' => "Just FULLY updated and re-recorded with all new React features for 2023 (React v18)! Join a live online community of over 900,000+ developers and a course taught                   by industry experts that have actually worked both in Silicon Valley and Toronto with React.js.
                                  Using the latest version of React (React 18), this course is focused on efficiency. Never spend time on confusing, out of date, incomplete tutorials anymore. Graduates of Andrei’s courses are now working at Google, Tesla, Amazon, Apple, IBM, JP Morgan, Meta, + other top tech companies.",
                'sub_title' => 'Updated! Become a Senior React Developer. Build a massive E-commerce app with Redux, Hooks, GraphQL, Stripe, Firebase',
                'language' => 2,
                'type' => 2,
                'status' => 3,
                'category_id' => 1002,
                'max_participants' => 17,
                'level' => 3,
                'trainer_id' => 1000,
                'price' => 84.99,
                'currency' => 'USD',
            ]);
            Course::firstOrCreate([
                'id' => 1006,
                'user_id' => 999,
                'cover_image' => 'images/course/react.png',
                'promo_video' => 'videos/course/intro.mp4',
                'title' => 'Build Web Apps with React & Firebase',
                'description' => "React is a hugely popular front-end library and React developers are always in hight demand in the web dev job market. In this course you'll learn how to use React                 from the ground-up to create dynamic & interactive websites, and by the time you finish you'll be in a great position to succeed in a job as a React developer.
                                  You'll also have 4 full React projects under your belt too, which you can customize and use in your portfolio!",
                'sub_title' => 'Learn React from the ground up to make dynamic websites (includes Context, Hooks, Reducers, Routing, Auth, Databases)',
                'language' => 2,
                'type' => 2,
                'status' => 3,
                'category_id' => 1002,
                'max_participants' => 17,
                'level' => 3,
                'trainer_id' => 1000,
                'price' => 12.99,
                'currency' => 'USD',
            ]);
            Course::firstOrCreate([
                'id' => 1007,
                'user_id' => 999,
                'cover_image' => 'images/course/laravel.png',
                'promo_video' => 'videos/course/intro.mp4',
                'title' => 'Laravel 8 - Build Advance Ecommerce Project A-Z',
                'description' => "Laravel 8 Advance Ecommerce Project A-Z
                                    If you are very serious about learning laravel 8 from beginner to advance. Build up your laravel skill then this course will be the best choice for you. In this course, you will build three different projects. One will be How to build a company website with Laravel 8. Then Laravel 8 Multi Authentication and last you will build one complete Advance Ecommerce Project with Laravel 8. You will build every project from scratch. This is not just a functional course it's a real-life project course. Which helps you to become a professional developer.",
                'sub_title' => 'In This Course, You Will Build Three Different Project With Laravel 8 Include Advance Professional Ecommerce Site A-Z',
                'language' => 2,
                'type' => 1,
                'status' => 3,
                'category_id' => 1001,
                'max_participants' => 17,
                'level' => 1,
                'trainer_id' => 1001,
                'price' => 12.99,
                'currency' => 'USD',
            ]);

            WishList::firstOrCreate([
                'user_id' => 1002,
                'course_id' => 1002
            ]);
            WishList::firstOrCreate([
                'user_id' => 1002,
                'course_id' => 1005
            ]);
            WishList::firstOrCreate([
                'user_id' => 1003,
                'course_id' => 1003
            ]);
            WishList::firstOrCreate([
                'user_id' => 1003,
                'course_id' => 1006
            ]);
            WishList::firstOrCreate([
                'user_id' => 1004,
                'course_id' => 1004
            ]);
            WishList::firstOrCreate([
                'user_id' => 1004,
                'course_id' => 1005
            ]);
            BasketList::firstOrCreate([
                'user_id' => 1004,
                'course_id' => 1004
            ]);
            BasketList::firstOrCreate([
                'user_id' => 1004,
                'course_id' => 1003
            ]);
            BasketList::firstOrCreate([
                'user_id' => 1003,
                'course_id' => 1006
            ]);
            BasketList::firstOrCreate([
                'user_id' => 1003,
                'course_id' => 1005
            ]);
            BasketList::firstOrCreate([
                'user_id' => 1002,
                'course_id' => 1002
            ]);
            BasketList::firstOrCreate([
                'user_id' => 1002,
                'course_id' => 1007
            ]);

            Section::firstOrCreate([
                'id' => 1000,
                'title' => 'React',
                'course_id' => 1004
            ]);
            Section::firstOrCreate([
                'id' => 1001,
                'title' => 'Vue JS',
                'course_id' => 1004
            ]);
            Section::firstOrCreate([
                'id' => 1002,
                'title' => 'Angular JS',
                'course_id' => 1004
            ]);
            Section::firstOrCreate([
                'id' => 1003,
                'title' => 'Laravel back end',
                'course_id' => 1007
            ]);
            Section::firstOrCreate([
                'id' => 1004,
                'title' => 'Yii',
                'course_id' => 1007
            ]);
            Section::firstOrCreate([
                'id' => 1005,
                'title' => 'Symphony',
                'course_id' => 1007
            ]);

            Lesson::firstOrCreate([
                'id' => 1000,
                'title' => 'React js|introduction',
                'duration' => 3600,
                'course_id' => 1004,
                'video_url' => 'https://www.youtube.com/watch?v=j942wKiXFu8',
                'type' => 'course'
            ]);
            SectionLesson::firstOrCreate([
                'section_id' => 1000,
                'lesson_id' => 1000
            ]);
            Lesson::firstOrCreate([
                'id' => 1001,
                'title' => 'React js|Creating react application',
                'duration' => 3600,
                'course_id' => 1004,
                'video_url' => 'https://www.youtube.com/watch?v=j942wKiXFu8',
                'type' => 'course'
            ]);
            SectionLesson::firstOrCreate([
                'section_id' => 1000,
                'lesson_id' => 1001
            ]);
            Lesson::firstOrCreate([
                'id' => 1002,
                'title' => 'React js|Components, Templates',
                'duration' => 3600,
                'course_id' => 1004,
                'video_url' => 'https://www.youtube.com/watch?v=j942wKiXFu8',
                'type' => 'course'
            ]);
            SectionLesson::firstOrCreate([
                'section_id' => 1000,
                'lesson_id' => 1002
            ]);

            Lesson::firstOrCreate([
                'id' => 1003,
                'title' => 'Vue js components',
                'duration' => 3600,
                'course_id' => 1004,
                'video_url' => 'https://www.youtube.com/watch?v=j942wKiXFu8',
                'type' => 'course'
            ]);
            SectionLesson::firstOrCreate([
                'section_id' => 1001,
                'lesson_id' => 1003
            ]);
            Lesson::firstOrCreate([
                'id' => 1004,
                'title' => 'Vue js introduction',
                'duration' => 3600,
                'course_id' => 1004,
                'video_url' => 'https://www.youtube.com/watch?v=j942wKiXFu8',
                'type' => 'course'
            ]);
            SectionLesson::firstOrCreate([
                'section_id' => 1001,
                'lesson_id' => 1004
            ]);
            Lesson::firstOrCreate([
                'id' => 1005,
                'title' => 'Vue js router',
                'duration' => 3600,
                'course_id' => 1004,
                'video_url' => 'https://www.youtube.com/watch?v=j942wKiXFu8',
                'type' => 'course'
            ]);
            SectionLesson::firstOrCreate([
                'section_id' => 1001,
                'lesson_id' => 1005
            ]);


            Lesson::firstOrCreate([
                'id' => 1006,
                'title' => 'Angular js components',
                'duration' => 3600,
                'course_id' => 1004,
                'video_url' => 'https://www.youtube.com/watch?v=j942wKiXFu8',
                'type' => 'course'
            ]);
            SectionLesson::firstOrCreate([
                'section_id' => 1002,
                'lesson_id' => 1006
            ]);
            Lesson::firstOrCreate([
                'id' => 1007,
                'title' => 'Angular js introduction',
                'duration' => 3600,
                'course_id' => 1004,
                'video_url' => 'https://www.youtube.com/watch?v=j942wKiXFu8',
                'type' => 'course'
            ]);
            SectionLesson::firstOrCreate([
                'section_id' => 1002,
                'lesson_id' => 1007
            ]);
            Lesson::firstOrCreate([
                'id' => 1008,
                'title' => 'Angular js router',
                'duration' => 3600,
                'course_id' => 1004,
                'video_url' => 'https://www.youtube.com/watch?v=j942wKiXFu8',
                'type' => 'course'
            ]);
            SectionLesson::firstOrCreate([
                'section_id' => 1002,
                'lesson_id' => 1008
            ]);

            Lesson::firstOrCreate([
                'id' => 1009,
                'title' => 'Laravel components',
                'duration' => 3600,
                'course_id' => 1007,
                'video_url' => 'https://www.youtube.com/watch?v=j942wKiXFu8',
                'type' => 'course'
            ]);
            SectionLesson::firstOrCreate([
                'section_id' => 1003,
                'lesson_id' => 1009
            ]);
            Lesson::firstOrCreate([
                'id' => 1010,
                'title' => 'Laravel introduction',
                'duration' => 3600,
                'course_id' => 1007,
                'video_url' => 'https://www.youtube.com/watch?v=j942wKiXFu8',
                'type' => 'course'
            ]);
            SectionLesson::firstOrCreate([
                'section_id' => 1003,
                'lesson_id' => 1010
            ]);
            Lesson::firstOrCreate([
                'id' => 1011,
                'title' => 'Laravel router',
                'duration' => 3600,
                'course_id' => 1007,
                'video_url' => 'https://www.youtube.com/watch?v=j942wKiXFu8',
                'type' => 'course'
            ]);
            SectionLesson::firstOrCreate([
                'section_id' => 1003,
                'lesson_id' => 1008
            ]);
            Lesson::firstOrCreate([
                'id' => 1012,
                'title' => 'Yii introduction',
                'duration' => 3600,
                'course_id' => 1007,
                'video_url' => 'https://www.youtube.com/watch?v=j942wKiXFu8',
                'type' => 'course'
            ]);
            SectionLesson::firstOrCreate([
                'section_id' => 1004,
                'lesson_id' => 1012
            ]);

            Lesson::firstOrCreate([
                'id' => 1013,
                'title' => 'Yii components',
                'duration' => 3600,
                'course_id' => 1007,
                'video_url' => 'https://www.youtube.com/watch?v=j942wKiXFu8',
                'type' => 'course'
            ]);
            SectionLesson::firstOrCreate([
                'section_id' => 1004,
                'lesson_id' => 1013
            ]);

            Lesson::firstOrCreate([
                'id' => 1014,
                'title' => 'Yii router',
                'duration' => 3600,
                'course_id' => 1007,
                'video_url' => 'https://www.youtube.com/watch?v=j942wKiXFu8',
                'type' => 'course'
            ]);
            SectionLesson::firstOrCreate([
                'section_id' => 1004,
                'lesson_id' => 1014
            ]);


            Lesson::firstOrCreate([
                'id' => 1015,
                'title' => 'Symphony introduction',
                'duration' => 3600,
                'course_id' => 1007,
                'video_url' => 'https://www.youtube.com/watch?v=j942wKiXFu8',
                'type' => 'course'
            ]);
            SectionLesson::firstOrCreate([
                'section_id' => 1005,
                'lesson_id' => 1015
            ]);

            Lesson::firstOrCreate([
                'id' => 1016,
                'title' => 'Symphony components',
                'duration' => 3600,
                'course_id' => 1007,
                'video_url' => 'https://www.youtube.com/watch?v=j942wKiXFu8',
                'type' => 'course'
            ]);
            SectionLesson::firstOrCreate([
                'section_id' => 1005,
                'lesson_id' => 1016
            ]);
            Lesson::firstOrCreate([
                'id' => 1017,
                'title' => 'Symphony router',
                'duration' => 3600,
                'course_id' => 1007,
                'video_url' => 'https://www.youtube.com/watch?v=j942wKiXFu8',
                'type' => 'course'
            ]);
            SectionLesson::firstOrCreate([
                'section_id' => 1005,
                'lesson_id' => 1017
            ]);

            Quiz::firstOrCreate([
                'id' => 1000,
                'section_id' => 1000
            ]);

            QuizQuestion::firstOrCreate([
                'id' => 1000,
                'quiz_id' => 1000,
                'question' => 'What is Computer programming?',
                'answers' => json_encode(['writing', 'debugging', 'coding']),
                'right_answers' => json_encode(['coding']),
            ]);
            QuizQuestion::firstOrCreate([
                'id' => 1001,
                'quiz_id' => 1000,
                'question' => 'Name different types of errors which can occur during the execution of a program?',
                'answers' => json_encode(['Syntax Errors', 'Runtime Errors', 'Logical errors']),
                'right_answers' => json_encode(['Logical errors', 'Runtime Errors']),
            ]);


            Quiz::firstOrCreate([
                'id' => 1001,
                'section_id' => 1001
            ]);

            QuizQuestion::firstOrCreate([
                'id' => 1002,
                'quiz_id' => 1001,
                'question' => 'Name different types of loops.',
                'answers' => json_encode(['FOR…NEXT Loop', 'WHILE…WEND Loop', 'Nested Loop']),
                'right_answers' => json_encode(['Nested Loop']),
            ]);
            QuizQuestion::firstOrCreate([
                'id' => 1003,
                'quiz_id' => 1001,
                'question' => 'List some programming languages.',
                'answers' => json_encode(['A++', 'APL', 'COBOL', 'BASIC', 'C++']),
                'right_answers' => json_encode(['COBOL', 'C++']),
            ]);

            Quiz::firstOrCreate([
                'id' => 1002,
                'section_id' => 1002
            ]);

            QuizQuestion::firstOrCreate([
                'id' => 1004,
                'quiz_id' => 1002,
                'question' => 'What are constants? Explain their types.',
                'answers' => json_encode(['Numeric constants', 'String constants', 'Object constants']),
                'right_answers' => json_encode(['Numeric constants', 'String constants']),
            ]);
            QuizQuestion::firstOrCreate([
                'id' => 1005,
                'quiz_id' => 1002,
                'question' => 'Please explain the operators.',
                'answers' => json_encode(['Arithmetic', 'Assignment', 'Logical', 'Not logical', 'Relational', 'Syntax']),
                'right_answers' => json_encode(['Arithmetic', 'Assignment', 'Logical']),
            ]);

            Quiz::firstOrCreate([
                'id' => 1003,
                'section_id' => 1003
            ]);

            QuizQuestion::firstOrCreate([
                'id' => 1006,
                'quiz_id' => 1003,
                'question' => 'How is the comparison of objects done in PHP?',
                'answers' => json_encode(['=', '==', '===', '====']),
                'right_answers' => json_encode(['==', '===']),
            ]);
            QuizQuestion::firstOrCreate([
                'id' => 1007,
                'quiz_id' => 1003,
                'question' => 'Q8. What are the different types of PHP variables?',
                'answers' => json_encode(['Integers', 'Float', 'Resources', 'Itterable']),
                'right_answers' => json_encode(['Integers', 'Resources']),
            ]);

            Quiz::firstOrCreate([
                'id' => 1004,
                'section_id' => 1004
            ]);

            QuizQuestion::firstOrCreate([
                'id' => 1008,
                'quiz_id' => 1004,
                'question' => 'Name some of the constants  in PHP and their purpose.',
                'answers' => json_encode(['_LINE_', '__CONSTRUCTOR__', '__toString__']),
                'right_answers' => json_encode(['_LINE_']),
            ]);
            QuizQuestion::firstOrCreate([
                'id' => 1009,
                'quiz_id' => 1004,
                'question' => 'Name some of the popular frameworks in PHP.',
                'answers' => json_encode(['CakePHP', 'Yii 2', 'Pascal', 'Django']),
                'right_answers' => json_encode(['CakePHP', 'Yii 2']),
            ]);


            Quiz::firstOrCreate([
                'id' => 1005,
                'section_id' => 1005
            ]);

            QuizQuestion::firstOrCreate([
                'id' => 1010,
                'quiz_id' => 1005,
                'question' => ' What are the data types in PHP?',
                'answers' => json_encode(['Integer', 'Boolean', 'Float', 'Iterable']),
                'right_answers' => json_encode(['Integer', 'Boolean', 'Float']),
            ]);
            QuizQuestion::firstOrCreate([
                'id' => 1011,
                'quiz_id' => 1005,
                'question' => ' What are different types of errors available in Php ?',
                'answers' => json_encode(['E_ERROR', 'E_WARNING', 'E_FATAL_ERROR', 'E_FATAL']),
                'right_answers' => json_encode(['E_ERROR', 'E_WARNING']),
            ]);
        }

    }
}
