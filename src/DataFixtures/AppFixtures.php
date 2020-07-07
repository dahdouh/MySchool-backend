<?php

namespace App\DataFixtures;

use App\Entity\Choice;
use App\Entity\Course;
use App\Entity\Document;
use App\Entity\Event;
use App\Entity\Exam;
use App\Entity\Exercise;
use App\Entity\Forum;
use App\Entity\Kinship;
use App\Entity\Level;
use App\Entity\Member;
use App\Entity\Post;
use App\Entity\Question;
use App\Entity\Quiz;
use App\Entity\Subject;
use App\Entity\Subscription;
use App\Entity\Topic;
use App\Entity\Video;
use DateInterval;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private $passwordEncoder;
    private $container;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder, ContainerInterface $container)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->container = $container;
    }

    private function newSubscription(Member $student, array $levels)
    {
        $faker = Factory::create('fr_FR');

        $subscription = new Subscription();
        $subscription->setStudent($student);

        $durations = [
            1 => "+ 1 month",
            3 => "+ 3 months",
            6 => "+ 6 months",
            12 => "+ 1 year",
            24 => "+ 2 years"
        ];

        $chosenDurationValue = $faker->randomElement($durations);
        $chosenDurationKey = array_search($chosenDurationValue, $durations);

        $startDate = $faker->dateTimeBetween("-3 years", "now");
        $subscription->setDateStart($startDate);

        $endDate = new DateTime();
        $endDate->setTimestamp($startDate->getTimestamp());
        $endDate->add(DateInterval::createFromDateString($chosenDurationValue));
        $subscription->setDateEnd($endDate);

        if ($endDate < new DateTime()) {
            $subscription->setIsActive(false);
        } else {
            $subscription->setIsActive(true);
        }

        // choose a random level
        /** @var Level $subscriptionLevel */
        $subscription->setLevel($subscriptionLevel = $faker->randomElement($levels));

        // add random subjects from this level
        $numberSubjects = $faker->numberBetween(1, 5);
        for ($i = 0; $i < $numberSubjects; $i++) {
            $subscription->addSubject($faker->randomElement($subscriptionLevel->getSubjects()));
        }

        $subjectPrice = $this->container->getParameter("subscription_subjectPrice");
        $monthlyRate = $this->container->getParameter("subscription_monthlyRate");

        $totalPrice =
            ($numberSubjects * $subjectPrice) *
            pow($monthlyRate, $chosenDurationKey) *
            $chosenDurationKey;

        $subscription->setPrice(number_format($totalPrice, "2", ".", ""));

        return $subscription;
    }

    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('fr_FR');

        // object numbers

        $n_members = 10;
        $n_courses = 10;
        $n_videos = 10;
        $n_documents = 10;
        $n_quizzes = 10;
        $n_exercises = 10;
        $n_exams = 10;
        $n_topics = 10;
        $n_posts = 10;
        $n_events = 10;

        // Subject

        $maths = new Subject();
        $maths->setName("Mathématiques");
        $manager->persist($maths);

        $french = new Subject();
        $french->setName("Français");
        $manager->persist($french);

        $english = new Subject();
        $english->setName("Anglais");
        $manager->persist($english);

        $spanish = new Subject();
        $spanish->setName("Espagnol");
        $manager->persist($spanish);

        $history = new Subject();
        $history->setName("Histoire");
        $manager->persist($history);

        $geography = new Subject();
        $geography->setName("Géographie");
        $manager->persist($geography);

        $biology = new Subject();
        $biology->setName("SVT");
        $manager->persist($biology);

        $economy = new Subject();
        $economy->setName("Economie");
        $manager->persist($economy);

        // Level

        $level6eme = new Level();
        $level6eme->setName("Sixième");
        $level6eme->addSubject($maths);
        $level6eme->addSubject($french);
        $level6eme->addSubject($english);
        $level6eme->addSubject($history);
        $level6eme->addSubject($geography);
        $manager->persist($level6eme);

        $level5eme = new Level();
        $level5eme->setName("Cinquième");
        $level5eme->addSubject($maths);
        $level5eme->addSubject($french);
        $level5eme->addSubject($english);
        $level5eme->addSubject($history);
        $level5eme->addSubject($geography);
        $manager->persist($level5eme);

        $level4eme = new Level();
        $level4eme->setName("Quatrième");
        $level4eme->addSubject($maths);
        $level4eme->addSubject($french);
        $level4eme->addSubject($english);
        $level4eme->addSubject($spanish);
        $level4eme->addSubject($history);
        $level4eme->addSubject($geography);
        $manager->persist($level4eme);

        $level3eme = new Level();
        $level3eme->setName("Troisième");
        $level3eme->addSubject($maths);
        $level3eme->addSubject($french);
        $level3eme->addSubject($english);
        $level3eme->addSubject($spanish);
        $level3eme->addSubject($history);
        $level3eme->addSubject($geography);
        $level3eme->addSubject($biology);
        $manager->persist($level3eme);

        $levelSeconde = new Level();
        $levelSeconde->setName("Seconde");
        $levelSeconde->addSubject($maths);
        $levelSeconde->addSubject($french);
        $levelSeconde->addSubject($english);
        $levelSeconde->addSubject($spanish);
        $levelSeconde->addSubject($history);
        $levelSeconde->addSubject($geography);
        $levelSeconde->addSubject($biology);
        $manager->persist($levelSeconde);

        $levelPremiereL = new Level();
        $levelPremiereL->setName("Première L");
        $levelPremiereL->addSubject($maths);
        $levelPremiereL->addSubject($french);
        $levelPremiereL->addSubject($english);
        $levelPremiereL->addSubject($spanish);
        $levelPremiereL->addSubject($history);
        $levelPremiereL->addSubject($geography);
        $manager->persist($levelPremiereL);

        $levelPremiereS = new Level();
        $levelPremiereS->setName("Première S");
        $levelPremiereS->addSubject($maths);
        $levelPremiereS->addSubject($french);
        $levelPremiereS->addSubject($english);
        $levelPremiereS->addSubject($spanish);
        $levelPremiereS->addSubject($history);
        $levelPremiereS->addSubject($geography);
        $levelPremiereS->addSubject($biology);
        $manager->persist($levelPremiereS);

        $levelPremiereES = new Level();
        $levelPremiereES->setName("Première ES");
        $levelPremiereES->addSubject($maths);
        $levelPremiereES->addSubject($french);
        $levelPremiereES->addSubject($english);
        $levelPremiereES->addSubject($spanish);
        $levelPremiereES->addSubject($history);
        $levelPremiereES->addSubject($geography);
        $levelPremiereES->addSubject($economy);
        $manager->persist($levelPremiereES);

        $levelTerminaleL = new Level();
        $levelTerminaleL->setName("Terminale L");
        $levelTerminaleL->addSubject($maths);
        $levelTerminaleL->addSubject($french);
        $levelTerminaleL->addSubject($english);
        $levelTerminaleL->addSubject($spanish);
        $levelTerminaleL->addSubject($history);
        $levelTerminaleL->addSubject($geography);
        $manager->persist($levelTerminaleL);

        $levelTerminaleS = new Level();
        $levelTerminaleS->setName("Terminale S");
        $levelTerminaleS->addSubject($maths);
        $levelTerminaleS->addSubject($french);
        $levelTerminaleS->addSubject($english);
        $levelTerminaleS->addSubject($spanish);
        $levelTerminaleS->addSubject($history);
        $levelTerminaleS->addSubject($geography);
        $levelTerminaleS->addSubject($biology);
        $manager->persist($levelTerminaleS);

        $levelTerminaleES = new Level();
        $levelTerminaleES->setName("Terminale ES");
        $levelTerminaleES->addSubject($maths);
        $levelTerminaleES->addSubject($french);
        $levelTerminaleES->addSubject($english);
        $levelTerminaleES->addSubject($spanish);
        $levelTerminaleES->addSubject($history);
        $levelTerminaleES->addSubject($geography);
        $levelTerminaleES->addSubject($economy);
        $manager->persist($levelTerminaleES);

        $levels = [
            $level6eme,
            $level5eme,
            $level4eme,
            $level3eme,
            $levelSeconde,
            $levelPremiereL,
            $levelPremiereS,
            $levelPremiereES,
            $levelTerminaleL,
            $levelTerminaleS,
            $levelTerminaleES,
        ];

        // admin

        $admin = new Member();
        $admin->setEmail("admin@gmail.com");
        $admin->setFirstName("Admin");
        $admin->setLastName("Admin");
        $admin->setDateRegistration($faker->dateTime);
        $admin->setDateBirth($faker->dateTimeBetween("-100 years", "-10 years"));
        $admin->setIsActive(true);
        $admin->setRoles([Member::$ROLE_ADMIN["code"]]);
        $admin->setPassword(
            $this->passwordEncoder->encodePassword(
                $admin, '123')
        );

        $manager->persist($admin);

        // teacher

        $teacher = new Member();
        $teacher->setEmail("enseignant@gmail.com");
        $teacher->setFirstName($faker->firstName);
        $teacher->setLastName($faker->lastName);
        $teacher->setDateRegistration($faker->dateTime);
        $teacher->setDateBirth($faker->dateTimeBetween("-100 years", "-10 years"));
        $teacher->setIsActive(true);
        $teacher->setRoles([Member::$ROLE_TEACHER["code"]]);
        $teacher->setPassword(
            $this->passwordEncoder->encodePassword(
                $teacher, '123')
        );

        $manager->persist($teacher);

        // student

        // more than 18 years old
        $student = new Member();
        $student->setEmail("etudiant@gmail.com");
        $student->setFirstName($faker->firstName);
        $student->setLastName($faker->lastName);
        $student->setDateRegistration($faker->dateTime);
        $student->setDateBirth(new DateTime("01/01/1999"));
        $student->setIsActive(true);
        $student->setRoles([Member::$ROLE_STUDENT["code"]]);
        $student->setPassword(
            $this->passwordEncoder->encodePassword(
                $student, '123')
        );

        $manager->persist($student);

        $manager->persist($this->newSubscription($student, $levels));

        // less than 18 years old
        $student2 = new Member();
        $student2->setEmail("etudiant.mineur@gmail.com");
        $student2->setFirstName($faker->firstName);
        $student2->setLastName($faker->lastName);
        $student2->setDateRegistration($faker->dateTime);
        $student2->setDateBirth(new DateTime("01/01/2005"));
        $student2->setIsActive(true);
        $student2->setRoles([Member::$ROLE_STUDENT["code"]]);
        $student2->setPassword(
            $this->passwordEncoder->encodePassword(
                $student2, '123')
        );

        $manager->persist($student2);

        $manager->persist($this->newSubscription($student2, $levels));

        // tutor

        $tutor = new Member();
        $tutor->setEmail("parent@gmail.com");
        $tutor->setFirstName($faker->firstName);
        $tutor->setLastName($faker->lastName);
        $tutor->setDateRegistration($faker->dateTime);
        $tutor->setDateBirth($faker->dateTimeBetween("-100 years", "-10 years"));
        $tutor->setIsActive(true);
        $tutor->setRoles([Member::$ROLE_TUTOR["code"]]);
        $tutor->setPassword(
            $this->passwordEncoder->encodePassword(
                $tutor, '123')
        );

        $manager->persist($tutor);

        // random members
        $teachers = [$teacher]; // collect teachers for course
        $tutors = [$tutor]; // collect tutors for kinship
        $students = [$student]; // collect students for kinship
        $members = [$teacher, $student, $tutor]; // collect members

        for ($i = 0; $i < $n_members; $i++) {
            $member = new Member();
            $member->setEmail($faker->email);
            $member->setFirstName($faker->firstName);
            $member->setLastName($faker->lastName);
            $member->setDateRegistration($faker->dateTime);
            $member->setDateBirth($faker->dateTimeBetween("-100 years", "-10 years"));
            $member->setIsActive($faker->boolean(80));
            $member->setRoles([$role = $faker->randomElement(Member::getRoleTypesExceptAdmin())["code"]]);
            $member->setPassword(
                $this->passwordEncoder->encodePassword(
                    $member, '123')
            );

            // collect teachers for course
            if ($role == Member::$ROLE_TEACHER["code"]) {
                $teachers[] = $member;
            }

            // collect tutors for kinship
            if ($role == Member::$ROLE_TUTOR["code"]) {
                $tutors[] = $member;
            }

            // create a random subscription for all students
            if ($role == Member::$ROLE_STUDENT["code"]) {
                $students[] = $member;

                $manager->persist($this->newSubscription($member, $levels));

                // create a random events for all students (desktop)
                for ($j = 0; $j < $n_events; $j++) {
                    $event = new Event();
                    $event->setStudent($member);
                    $event->setTitle($faker->sentence);
                    $event->setContent($faker->realText());
                    $event->setClass($faker->randomElement(Event::$CLASS));
                    $event->setStartDate($date = $faker->dateTimeThisMonth);
                    $event->setEndDate($date);

                    $manager->persist($event);
                }
            }

            $members[] = $member;

            $manager->persist($member);
        }

        // defined members

        $janeDoe = new Member();
        $janeDoe->setEmail("jane.doe@gmail.com");
        $janeDoe->setFirstName("Jane");
        $janeDoe->setLastName("Doe");
        $janeDoe->setIsActive(true);
        $janeDoe->setDateRegistration($faker->dateTime);
        $janeDoe->setDateBirth($faker->dateTimeBetween("-100 years", "-30 years"));
        $janeDoe->setRoles(["ROLE_TUTOR"]);
        $janeDoe->setPassword(
            $this->passwordEncoder->encodePassword(
                $janeDoe, '123')
        );

        $manager->persist($janeDoe);

        $johnDoe = new Member();
        $johnDoe->setEmail("john.doe@gmail.com");
        $johnDoe->setFirstName("John");
        $johnDoe->setLastName("Doe");
        $johnDoe->setIsActive(true);
        $johnDoe->setDateRegistration($faker->dateTime);
        $johnDoe->setDateBirth($faker->dateTimeBetween("-17 years", "-10 years"));
        $johnDoe->setRoles(["ROLE_STUDENT"]);
        $johnDoe->setPassword(
            $this->passwordEncoder->encodePassword(
                $johnDoe, '123')
        );

        $manager->persist($johnDoe);

        // Kinship

        $doeKinship = new Kinship();
        $doeKinship->setStudent($johnDoe);
        $doeKinship->setTutor($janeDoe);
        $manager->persist($doeKinship);

        // Collect students that already are associated to a tutor
//        $studentsAlreadyInKinship = [];
//        foreach ($tutors as $tutor) {
//            // don't associate a student to a tutor that already has been
//            $randomStudent = $faker->randomElement($students);
//            if (!in_array($randomStudent, $studentsAlreadyInKinship)) {
//                $kinship = new Kinship();
//                $kinship->setTutor($tutor);
//                $kinship->setStudent($randomStudent);
//                $manager->persist($kinship);
//                $studentsAlreadyInKinship[] = $randomStudent;
//            }
//        }

        // course

        $courses = []; // collect course
        for ($i = 0; $i < $n_courses; $i++) {
            $course = new Course();
            $course->setName($faker->sentence);
            $course->setDescription($faker->text(150));
            $course->setContent($faker->realText(1000));
            $course->setIsValid($faker->boolean(70));
            /** @var Level $level */
            $course->setLevel($level = $faker->randomElement($levels));
            $course->setSubject($faker->randomElement($level->getSubjects()));
            $course->setTeacher($faker->randomElement($teachers));
            $course->setDateCreation($date_creation = $faker->dateTime);
            $course->setDateUpdate($date_creation);

            $manager->persist($course);
            $courses[] = $course;
        }

        // video

        for ($i = 0; $i < $n_videos; $i++) {
            $video = new Video();
            $video->setName($faker->realText(20));
            $video->setDescription($faker->realText());
            $video->setCourse($faker->randomElement($courses));

            $manager->persist($video);
        }

        // document

        for ($i = 0; $i < $n_documents; $i++) {
            $document = new Document();
            $document->setName($faker->realText(20));
            $document->setDescription($faker->realText());
            $document->setCourse($faker->randomElement($courses));

            $manager->persist($document);
        }

        // exercise

        for ($i = 0; $i < $n_exercises; $i++) {
            $exercise = new Exercise();
            $exercise->setContent($faker->realText());
            $exercise->setCorrection($faker->realText());
            $exercise->setCourse($faker->randomElement($courses));

            $manager->persist($exercise);
        }

        // exam

        for ($i = 0; $i < $n_exams; $i++) {
            $exam = new Exam();
            $exam->setContent($faker->realText());
            $exam->setCorrection($faker->realText());
            $exam->setCourse($faker->randomElement($courses));

            $manager->persist($exam);
        }

        // quiz

        for ($i = 0; $i < $n_quizzes; $i++) {
            $quiz = new Quiz();
            $quiz->setCourse($faker->randomElement($courses));
            $manager->persist($quiz);

            for ($j = 0; $j < $faker->numberBetween(1, 5); $j++) {
                $question = new Question();
                $question->setContent($faker->sentence);
                $question->setQuiz($quiz);
                $manager->persist($question);

                $rightChoice = new Choice();
                $rightChoice->setContent($faker->sentence);
                $rightChoice->setQuestion($question);
                $rightChoice->setIsAnswer(true);
                $manager->persist($rightChoice);

                for ($j = 0; $j < $faker->numberBetween(2, 5); $j++) {
                    $choice = new Choice();
                    $choice->setContent($faker->sentence);
                    $choice->setQuestion($question);
                    $choice->setIsAnswer(false);
                    $manager->persist($choice);
                }
            }
        }


        // forum

        $forums = []; // collect forums

        foreach ($levels as $level){
            $forum = new Forum();
            $forum->setLevel($level);

            $manager->persist($forum);
            $forums[] = $forum;
        }

        // topic

        foreach ($forums as $forum) {
            for ($i = 0; $i < $n_topics; $i++) {
                $topic = new Topic();
                $topic->setSubject($faker->randomElement($forum->getLevel()->getSubjects()));
                $topic->setAuthor($faker->randomElement(array_merge($students, $teachers)));
                $topic->setForum($forum);
                $topic->setTitle($faker->sentence);
                $topic->setContent($faker->text(150));
                $topic->setDate($faker->dateTime);

                $manager->persist($topic);

                // post

                for ($j = 0; $j < $n_posts; $j++){
                    $post = new Post();
                    $post->setAuthor($faker->randomElement(array_merge($students, $teachers)));
                    $post->setTopic($topic);
                    $post->setContent($faker->text);
                    $post->setDate($faker->dateTime);

                    $manager->persist($post);
                }
            }
        }

        $manager->flush();
    }
}
