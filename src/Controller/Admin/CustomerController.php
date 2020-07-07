<?php


namespace App\Controller\Admin;

use App\Entity\Course;
use App\Entity\Level;
use App\Entity\Subject;
use App\Entity\Member;
use App\Entity\Subscription;
use App\Repository\CourseRepository;
use App\Repository\LevelRepository;
use App\Repository\MemberRepository;
use App\Repository\SubscriptionRepository;
use App\Repository\ExerciseRepository;
use App\Repository\SubjectRepository;
use App\Repository\HistoricActionRepository;
use App\Repository\ForumRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use DateTime;
use DateInterval;


 
class CustomerController extends AbstractController
{
    private $levelRepository;
    private $passwordEncoder;

    public function __construct(LevelRepository $levelRepository, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->levelRepository = $levelRepository;
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * @Route("/api/login/{email}/{password}", name="api_login", methods={"GET"})
     */
    public function api_login(Request $request, string $email, string $password, EntityManagerInterface $manager, LevelRepository $levelRepository, MemberRepository $memberRepository)
    {
        //$user = $memberRepository->find(22);
        $user = $memberRepository->findOneBy(['email' => $email]);
        if($user != null) {
            $valid = $this->passwordEncoder->isPasswordValid($user, $password);
            if($valid) {
                //dd($valid); die();
                return $this->json($user, 200, [], ['groups'=> 'post:read']);      
            } else {
                $user->setEmail("not found");
                return $this->json($user, 200, [], ['groups'=> 'post:read']);  
            }
            
        } else {
            $user->setEmail("not found");
            return $this->json($user, 200, [], ['groups'=> 'post:read']);  
            //return $this->json(["email", "not found"], 200, [], ['groups'=> 'post:read']);      
                
        }
     
        //return $this->json($levelRepository->findAll(), 200, [], ['groups'=> 'post:read']);      

    }

    /**
     * @Route("/api/user/{id}", name="api_get_user_by_id", methods={"GET"})
     */
    public function api_getUserById(Request $request, string $id, EntityManagerInterface $manager, MemberRepository $memberRepository)
    {
        $user = $memberRepository->find($id);
        if($user != null) {
                return $this->json($user, 200, [], ['groups'=> 'post:read']);                  
        } else {
            return $this->json(["email", "not found"], 200, [], ['groups'=> 'post:read']);      
        }
    }


    /**
     * @Route("/api/password/reset/{email}", name="api_password_reset", methods={"GET"})
     */
    public function api_resetPassword(Request $request, string $email,  \Swift_Mailer $mailer, MemberRepository $memberRepository, UserPasswordEncoderInterface $encoder, EntityManagerInterface $em)
    {
        $user = $memberRepository->findOneBy(['email' => $email]);
        if($user != null) {
                    $plainPassword = 'schoolax';
                    $password = $encoder->encodePassword($user, $plainPassword);

                    $user->setPassword($password);                    
                    $em->persist($user);
                    $em->flush();

                    $message = (new \Swift_Message('Password reset'))
                    ->setFrom('onlineschool606@gmail.com')
                    ->setTo($email)
                    ->setBody("Hi, You have requested a new password. To access the MySchool application, you can use the following coordinates. login : ".$email." and password : ".$plainPassword);
            
                $mailer->send($message);

                $user->setPassword("schoolax");

                return $this->json($user, 200, [], ['groups'=> 'post:read']);                  
        } else {
            $user->setEmail("not found");
            return $this->json($user, 200, [], ['groups'=> 'post:read']);  
        }
    }

    /**
     * @Route("/api/profile/{id}", name="api_password_reset", methods={"GET"})
     */
    public function api_profile(Request $request, string $id,  MemberRepository $memberRepository, EntityManagerInterface $em)
    {
        $user = $memberRepository->find($id);
        if($user != null) {
                return $this->json($user, 200, [], ['groups'=> 'post:read']);                  
        } else {
            $user->setEmail("not found");
            return $this->json($user, 200, [], ['groups'=> 'post:read']);
        }
    }

    /**
     * @Route("/api/cours/{id}", name="api_cours_list", methods={"GET"})
     */
    public function list_cours_student(Request $request, string $id,  MemberRepository $memberRepository, EntityManagerInterface $em)
    {
        //$user = $memberRepository->find($id);
      //$query = $em->createQuery("SELECT c FROM App\Entity\Course sb JOIN sb.student st JOIN sb.level lv JOIN lv.courses c  WHERE sb.isActive=1 AND st.id=".$id."");
        $query = $em->createQuery("SELECT c FROM App\Entity\Course c JOIN c.level lv JOIN lv.subscriptions sb JOIN sb.student st  WHERE sb.isActive=1 AND st.id=".$id."");
        $users = $query->getResult();


        if($users != null) {
                return $this->json($users, 200, [], ['groups'=> 'post:read']);                  
        } else {
            return $this->json([], 200, [], ['groups'=> 'post:read']);
        }
    }

    /**
     * @Route("/api/exercice/{id}", name="api_exercice_list", methods={"GET"})
     */
    public function list_exercice_list(Request $request, string $id,  MemberRepository $memberRepository, EntityManagerInterface $em)
    {
        $query = $em->createQuery("SELECT e FROM App\Entity\Exercise e JOIN e.course c WHERE c.id=".$id."");
        $exercices = $query->getResult();


        if($exercices != null) {
                return $this->json($exercices, 200, [], ['groups'=> 'post:read']);                  
        } else {
            return $this->json(["exercice", "not found"], 200, [], ['groups'=> 'post:read']);
        }
    }

    /**
     * @Route("/api/exercice/correction/{id}", name="api_exercice_correction", methods={"GET"})
     */
    public function list_exercice_correction(Request $request, string $id,  ExerciseRepository $exerciseRepository, EntityManagerInterface $em)
    {
        $exercice = $exerciseRepository->find($id);
        //$query = $em->createQuery("SELECT e FROM App\Entity\Exercise e WHERE e.id=".$id."");
        //$exercice = $query->getResult();


        if($exercice != null) {
                return $this->json($exercice, 200, [], ['groups'=> 'post:read']);                  
        } else {
            return $this->json(["exercice", "not found"], 200, [], ['groups'=> 'post:read']);
        }
    }


    /**
     * @Route("/api/register/{email}/{password}/{firstName}/{lastName}/{birthday}/{role}", name="api_register", methods={"GET"})
     */
    public function api_register(Request $request, string $email, string $password, string $firstName, string $lastName, string $birthday, string $role,
                                 EntityManagerInterface $manager, UserPasswordEncoderInterface $encoder, MemberRepository $memberRepository)
    {
        if (is_null(array_search($role, Member::getRoleTypesExceptAdmin()))) {
            throw new Exception("Ce rôle n'est pas disponible.");
        }

        $user = $memberRepository->findOneBy(['email' => $email]);
        if($user != null) {
            $user->setEmail("already exist");
            return $this->json($user, 200, [], ['groups'=> 'post:read']);  
        }
        $member = new Member();
        $member->setEmail($email);
        $member->setPassword($encoder->encodePassword($member, $password));       
        $member->setLastName($firstName);
        $member->setFirstName($lastName);
        $member->setDateBirth(new DateTime($birthday));
        $roles[] = $role;
        $member->setRoles($roles);
        $member->setDateRegistration(new DateTime("now"));
        $manager->persist($member);
        $manager->flush();

        return $this->json($member, 200, [], ['groups'=> 'post:read']);                  
    }


    /**
     * @Route("/api/subscription/list/{id}", name="api_subscription_list")
     */
    public function api_subscription_list(Request $request, int $id, EntityManagerInterface $em,  
                    MemberRepository $memberRepository, SubscriptionRepository $subscriptionRepository, LevelRepository $levelRepository,  SubjectRepository $subjectRepository)
    {
         //$query = $em->createQuery("SELECT lv FROM App\Entity\Level lv  JOIN lv.courses c JOIN lv.subscriptions sb JOIN sb.student st  WHERE sb.isActive=1 AND st.id=".$id."");
        $query = $em->createQuery("SELECT sb FROM App\Entity\Subscription sb JOIN sb.student s JOIN sb.subjects sj WHERE sb.isActive=1 AND s.id=".$id."");
        $subscriptions = $query->getResult();

        return $this->json($subscriptions, 200, [], ['groups'=> 'post:read']);                  

     }

    /**
     * @Route("/api/subscription/new/{id}/{level}/{subject}/{duration}", name="api_subscription_new")
     */
    public function api_subscription_new(Request $request, int $id, string $level, string $subject, string $duration, EntityManagerInterface $manager,  
                    MemberRepository $memberRepository, SubscriptionRepository $subscriptionRepository, LevelRepository $levelRepository,  SubjectRepository $subjectRepository)
    {
        
        $user = $memberRepository->find($id);
        $level = $levelRepository->findOneBy(['name' => $level]);
        $subject = $subjectRepository->findOneBy(['name' => $subject]);

        $subscription = new Subscription(); 
        $subscription->setStudent($user);
        $subscription->setLevel($level);
        $subscription->addSubject($subject);

        $months = 0;
        switch ($duration) {
            case "1 mois":
                $months = 1;
                break;
            case "3 mois":
                $months = 3;
                break;
            case "6 mois":
                $months = 6;
                break;
            case "1 an":
                $months = 12;
                break;  
            case "2 ans":
                $months = 24;
                break;  
        }
        $startDate = new DateTime();
        $endDate = new DateTime();
        $endDate->setTimestamp($startDate->getTimestamp());
        $endDate->add(DateInterval::createFromDateString("+ " . $months . " months"));
        $subscription->setDateStart($startDate);
        $subscription->setDateEnd($endDate);
        $subscription->setIsActive(true);
        $totalPrice = 1 * $months * 7;
        $subscription->setPrice(number_format($totalPrice, "2", ".", ""));

        $manager->persist($subscription);
        $manager->flush();

        return $this->json($subscription, 200, [], ['groups'=> 'post:read']);                  

     }

    /**
     * @Route("/api/subscription/delete/{id}", name="api_subscription_delete")
     */
    public function api_subscription_delete(Request $request, int $id, EntityManagerInterface $manager,  
                    MemberRepository $memberRepository, SubscriptionRepository $subscriptionRepository, LevelRepository $levelRepository,  SubjectRepository $subjectRepository)
    {        

        $subscription = $subscriptionRepository->find($id);; 
        $subscription->setIsActive(false);
    
        $manager->persist($subscription);
        $manager->flush();

        return $this->json($subscription, 200, [], ['groups'=> 'post:read']); 
     }

    /**
     * @Route("/api/history/list/{id}", name="api_history_list")
     */
    public function api_history_list(Request $request, int $id, EntityManagerInterface $em, HistoricActionRepository $historicActionRepository)
    {        
        $query = $em->createQuery("SELECT his FROM App\Entity\HistoricAction his JOIN his.actor a WHERE a.id=".$id."");
        $history = $query->getResult();

        return $this->json($history, 200, [], ['groups'=> 'post:read']);  

     }

     /**
     * @Route("/api/forum/list/{id}", name="api_forum_list")
     */
    public function api_forum_list(Request $request, int $id, EntityManagerInterface $em, ForumRepository $forumRepository)
    {        
        
        //$query = $em->createQuery("SELECT lv.id FROM App\Entity\Level lv JOIN lv.subscriptions sb JOIN sb.student s WHERE sb.isActive=1 AND s.id=".$id." GROUP BY lv.id");
        //$levels = $query->getArrayResult();

        $query = $em->createQuery("SELECT tp FROM App\Entity\Topic tp JOIN tp.forum f JOIN f.level l WHERE l.id in (SELECT lv.id FROM App\Entity\Level lv JOIN lv.subscriptions sb JOIN sb.student s WHERE sb.isActive=1 AND s.id=".$id." GROUP BY lv.id)");
        //$query = $em->createQuery("SELECT his FROM App\Entity\Forum his JOIN his.actor a WHERE a.id=".$id."");
        $topics = $query->getResult();
 

        //return $this->json($levels, 200, [], ['groups'=> 'post:read']);  
        return $this->json($topics[0], 200, [], ['groups'=> 'post:read']);  

     }

    



    /**
     * @Route("/api/post", name="add_level", methods={"POST"})
     */
    public function saveLevel(Request $request, EntityManagerInterface $em, SerializerInterface $serializer, LevelRepository $levelRepository)
    {
        $jsonRecu = $request->getContent();
 
        $level = $serializer->deserialize($jsonRecu, Level::class, 'json');
        $em->persist($level);
        $em->flush();
        
        return $this->json($level, 201, [], ['groups'=> 'post:read']);
 
        
    }

    /**
     * @Route("/api/level/get/{niveau}", name="get_level", methods={"GET"})
     */
    public function getLevel(Request $request, string $niveau, EntityManagerInterface $em, SerializerInterface $serializer, LevelRepository $levelRepository)
    {
           
        $level = new Level();
        $level->setName($niveau);
 
        $em->persist($level);
        $em->flush();
        
        return $this->json($level, 201, [], ['groups'=> 'post:read']);
 
        
    }
}