<?php


namespace App\Controller\Admin;

use App\Entity\Course;
use App\Entity\Level;
use App\Entity\Subject;
use App\Entity\Member;
use App\Entity\Subscription;
use App\Entity\Post;
use App\Entity\Payment;
use App\Entity\Kinship;
use App\Entity\HistoricAction;
use App\Repository\CourseRepository;
use App\Repository\LevelRepository;
use App\Repository\MemberRepository;
use App\Repository\SubscriptionRepository;
use App\Repository\ExerciseRepository;
use App\Repository\SubjectRepository;
use App\Repository\HistoricActionRepository;
use App\Repository\ForumRepository;
use App\Repository\PostRepository;
use App\Repository\TopicRepository;
use App\Repository\KinshipRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use App\Service\CurrentMember;
use DateTime;
use DateInterval;

//image
use App\Form\MemberType;
use Symfony\Component\String\Slugger\SluggerInterface;
/*
use App\Controller\Admin\UploadedBase64File;
use App\Controller\Admin\Base64FileExtractor;
*/

 
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
            $user = new Member();;
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
                    /*
                    $message = (new \Swift_Message('Password reset'))
                    ->setFrom('onlineschool606@gmail.com')
                    ->setTo("karim.dahdouh.fr@gmail.com")
                    ->setBody("Hi, You have requested a new password. To access the MySchool application, you can use the following coordinates. login : ".$email." and password : ".$plainPassword);
                    */
                    $message = (new \Swift_Message('SchoolAx password reset'))
                    ->setFrom('onlineschool606@gmail.com')
                    ->setTo($user->getEmail())
                    ->setBody(
                        $this->renderView(
                            // templates/emails/registration.html.twig
                            'emails/password.html.twig',
                            ['name' => $user->getFirstName(), 'login' => $user->getEmail(), 'password' => $plainPassword]
                        ),
                        'text/html'
                    );
            
                    $mailer->send($message);

                
                $user->setPassword("schoolax");

                return $this->json($user, 200, [], ['groups'=> 'post:read']);                  
        } else {
            $user->setEmail("not found");
            return $this->json($user, 200, [], ['groups'=> 'post:read']);  
        }
        
    }

    /**
     * @Route("/api/profile/{id}", name="api_profile", methods={"GET"})
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
     * @Route("/api/profile/picture/new/{id}", name="profile_picture_new")
     */

    public function profile_picture(Request $request, String $id, Base64FileExtractor $base64FileExtractor, SluggerInterface $slugger, MemberRepository $memberRepository, EntityManagerInterface $em)
    {
        $user = $memberRepository->find($id);
        if($user != null) {
        
            //$form = $this->createForm(MemberType::class, $user);
            //$form->handleRequest($request);

            $img = $_POST['image']; // data 'data:image/png;base64,AAAFBfj42Pj4';
            $img = str_replace('data:image/png;base64,', '', $img);
            $img = str_replace(' ', '+', $img);
            //$ini =substr($img[0], 11);
            //$type = explode(';', $ini);
            $data = base64_decode($img);
            $avatar = $id.'.jpg';
            file_put_contents($this->getParameter('picture_directory').'/'.$avatar, $data);

            $user = $memberRepository->find($id);
            $user->setImage($avatar);
            $em->persist($user);
            $em->flush();
                    
            return $this->json(true, 200, [], ['groups'=> 'post:read']);                  
             
        } else {
            $user->setEmail("not found");
            return $this->json(false, 200, [], ['groups'=> 'post:read']);
        }
        

       /* return $this->render('member/profileupdate.html.twig', [
            'form' => $form->createView(),
        ]);
        */
    }

    /*
    public function uploadimageAction(Request $request, String $id, MemberRepository $memberRepository, EntityManagerInterface $em)
    {
    	   		
    	$response = "No hay archivo subido";
    	
    	$base = $request->request->get('image');
    	$filename = $request->request->get('filename');
    	$propertyId = $request->request->get('id');

    	
    	if($base != null)
    	{
    		$uploadDir = $this->container->getParameter('upload_dir');
			$uploadSaveDir = $this->container->getParameter('upload_save_dir');
    		
    		// Decode Image
    		$binary=base64_decode($base);
    		header('Content-Type: bitmap; charset=utf-8');
    		// Images will be saved under 'www/imgupload/uploads' folder
    		$file = fopen($this->getParameter('picture_directory').'/'.$filename, 'wb');
    		// Create File
    		fwrite($file, $binary);
    		fclose($file);
    		$response = "Se ha publicado exitosamente la propiedad.";
            
            $user = $memberRepository->find($id);
            $user->setImage($newFilename);
            $em->persist($user);
            $em->flush();
            return $this->json($user, 200, [], ['groups'=> 'post:read']);
    		
    	
    	}
    	return $this->json([], 200, [], ['groups'=> 'post:read']);
    }
    */

    /*
    public function profile_picture(Request $request, String $id, SluggerInterface $slugger, MemberRepository $memberRepository, EntityManagerInterface $em)
    {
        $user = $memberRepository->find($id);
        if($user != null) {
        
            $form = $this->createForm(MemberType::class, $user);
            $form->handleRequest($request);

            if ($form->isSubmitted()) {
                $brochureFile = $form->get('image')->getData();

                if ($brochureFile) {
                    $originalFilename = pathinfo($brochureFile->getClientOriginalName(), PATHINFO_FILENAME);
                    // this is needed to safely include the file name as part of the URL
                    $safeFilename = $slugger->slug($originalFilename);
                    $newFilename = $safeFilename.'-'.uniqid().'.'.$brochureFile->guessExtension();

                    // Move the file to the directory where brochures are stored
                    try {
                        $brochureFile->move(
                            $this->getParameter('picture_directory'),
                            $newFilename
                        );
                    } catch (FileException $e) {
                    }
                    // updates the 'brochureFilename' property to store the PDF file name
                    // instead of its contents
                    $user->setImage($newFilename);
                    $em->persist($user);
                    $em->flush();
                    return $this->json($user, 200, [], ['groups'=> 'post:read']);                  
                }
                return $this->json($user, 200, [], ['groups'=> 'post:read']);                  
            }
            
        } else {
            $user->setEmail("not found");
            return $this->json($user, 200, [], ['groups'=> 'post:read']);
        }
        

        return $this->render('member/profileupdate.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    */



    /**
     * @Route("/api/profile/update/{email}/{password}/{firstName}/{lastName}/{birthday}", name="api_profile_update", methods={"GET"})
     */
    public function api_profile_update(Request $request, string $email, string $password, string $firstName, string $lastName, string $birthday,  UserPasswordEncoderInterface $encoder, MemberRepository $memberRepository, EntityManagerInterface $em)
    {
        $user = $memberRepository->findOneBy(['email' => $email]);
        if($user === null) {
            $user->setEmail("already exist");
            return $this->json($user, 200, [], ['groups'=> 'post:read']);  
        }
        $member = new Member();
        $user->setEmail($email);
        $user->setPassword($encoder->encodePassword($member, $password));       
        $user->setLastName($firstName);
        $user->setFirstName($lastName);
        $user->setDateBirth(new DateTime($birthday));
        $em->persist($user);
        $em->flush();

        return $this->json($user, 200, [], ['groups'=> 'post:read']);                  
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
     * @Route("/api/subject/cours/{id}/{subject}", name="api_subject_cours_list", methods={"GET"})
     */
    public function suject_list_cours_student(Request $request, string $id, string $subject,  MemberRepository $memberRepository, EntityManagerInterface $em)
    {
        //$user = $memberRepository->find($id);
      //$query = $em->createQuery("SELECT c FROM App\Entity\Course sb JOIN sb.student st JOIN sb.level lv JOIN lv.courses c  WHERE sb.isActive=1 AND st.id=".$id."");
        $query = $em->createQuery("SELECT c FROM App\Entity\Course c JOIN c.subject subj JOIN c.level lv JOIN lv.subscriptions sb JOIN sb.student st  WHERE sb.isActive=1 AND st.id=".$id." AND subj.id=".$subject."");
        $users = $query->getResult();

        if($users != null) {
                return $this->json($users, 200, [], ['groups'=> 'post:read']);                  
        } else {
            return $this->json([], 200, [], ['groups'=> 'post:read']);
        }
    }

    /**
     * @Route("/api/subject/list", name="api_subject_list", methods={"GET"})
     */
    public function list_subject(Request $request,  MemberRepository $memberRepository, EntityManagerInterface $em)
    {
        $query = $em->createQuery("SELECT sb FROM App\Entity\Subject sb  ORDER BY sb.name ASC");
        $subjects= $query->getResult();
        return $this->json($subjects, 200, [], ['groups'=> 'post:read']);                  
    }

    /**
     * @Route("/api/cours/content/video/{id}", name="api_cours_content_video_list", methods={"GET"})
     */
    public function list_cours_content_video(Request $request, string $id, EntityManagerInterface $em)
    {
        $query = $em->createQuery("SELECT cont FROM App\Entity\CourseContent cont JOIN cont.course c WHERE cont.type=2 AND c.id=".$id."");
        $users = $query->getResult();

        if($users != null) {
                return $this->json($users, 200, [], ['groups'=> 'post:read']);                  
        } else {
            return $this->json([], 200, [], ['groups'=> 'post:read']);
        }
    }

    /**
     * @Route("/api/cours/content/pdf/{id}", name="api_cours_content_pdf_list", methods={"GET"})
     */
    public function list_cours_content_pdf(Request $request, string $id, EntityManagerInterface $em)
    {
        $query = $em->createQuery("SELECT cont FROM App\Entity\CourseContent cont JOIN cont.course c WHERE cont.type=1 AND c.id=".$id."");
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
     * @Route("/api/quiz/{course_id}", name="api_quiz_list", methods={"GET"})
     */
    public function list_quiz_list(Request $request, string $course_id,  MemberRepository $memberRepository, EntityManagerInterface $em)
    {
        $query = $em->createQuery("SELECT qu FROM App\Entity\Question qu JOIN qu.quiz qz JOIN qz.course c WHERE c.id=".$course_id." AND qu.response1!=''");
        $quiz = $query->getResult();


        if($quiz != null) {
                return $this->json($quiz, 200, [], ['groups'=> 'post:read']);                  
        } else {
            return $this->json(["quiz", "not found"], 200, [], ['groups'=> 'post:read']);
        }
    }


    /**
     * @Route("/api/register/{email}/{password}/{firstName}/{lastName}/{role}/{tel?}",  defaults={"tel": ""}, name="api_register", methods={"GET"})
     */
    public function api_register(Request $request, string $email, string $password, string $firstName, string $lastName, string $role, string $tel,
                                 EntityManagerInterface $manager, UserPasswordEncoderInterface $encoder, MemberRepository $memberRepository, \Swift_Mailer $mailer)
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
        $member->setTel($tel);
        $member->setIsActive(false);
        //$member->setDateBirth(new DateTime($birthday));
        $roles[] = $role;
        $member->setRoles($roles);
        $member->setDateRegistration(new DateTime("now"));
        $manager->persist($member);
        $manager->flush();


        $message = (new \Swift_Message('SchoolAx activate your account'))
                    ->setFrom('onlineschool606@gmail.com')
                    ->setTo($member->getEmail())
                    ->setBody(
                        $this->renderView(
                            // templates/emails/registration.html.twig
                            'emails/activate.html.twig',
                            ['name' => $member->getFirstName(), 'login' => $member->getEmail(), 'password' => $password]
                        ),
                        'text/html'
                    );
            
                    $mailer->send($message);
        

        

        return $this->json($member, 200, [], ['groups'=> 'post:read']);                  
    }

    /**
     * @Route("/api/register/activate/{email}", name="api_register_activate", methods={"GET"})
     */
    public function api_register_activate(Request $request, string $email, EntityManagerInterface $manager, UserPasswordEncoderInterface $encoder, MemberRepository $memberRepository, \Swift_Mailer $mailer)
    {

        $member = $memberRepository->findOneBy(['email' => $email]);
        if($member != null) {
             
            $member->setIsActive(true);
            $manager->persist($member);
            $manager->flush();

            $message = (new \Swift_Message('SchoolAx activate your account'))
                        ->setFrom('onlineschool606@gmail.com')
                        ->setTo($member->getEmail())
                        ->setBody(
                            $this->renderView(
                                // templates/emails/registration.html.twig
                                'emails/activate_done.html.twig',
                                ['name' => $member->getFirstName(), 'login' => $member->getEmail()]
                            ),
                            'text/html'
                        );
                
                        $mailer->send($message);
            

            return $this->render('emails/activate_done.html.twig', ["name" => $member->getFirstName() ]);    
        }           
    }

    /**
     * @Route("/api/register/child/{user_connected}/{login}/{password}/{firstName}/{lastName}/{birthday}/{level}/{type_subscription}/{email?}",  defaults={"email": ""}, name="api_register_child", methods={"GET"})
     */
    public function api_register_child(Request $request, string $user_connected, string $login, string $password, string $firstName, string $lastName, string $birthday,  string $level, string $type_subscription,  string $email, EntityManagerInterface $em, UserPasswordEncoderInterface $encoder, MemberRepository $memberRepository, LevelRepository $levelRepository,  SubjectRepository $subjectRepository)
    {
        
        $user = $memberRepository->findOneBy(['email' => $login]);
        if($user != null) {
            $user->setEmail("already exist");
            return $this->json($user, 200, [], ['groups'=> 'post:read']);  
        }

        // save student infos
        $member = new Member();
        $member->setEmail($email);
        $member->setPassword($encoder->encodePassword($member, $password)); 
        $member->setImage("default.png");      
        $member->setLastName($firstName);
        $member->setFirstName($lastName);
        $member->setEmail($email);
        $member->setDateBirth(new DateTime($birthday));        
        $member->setDateRegistration(new DateTime("now"));
        $roles[] = "ROLE_STUDENT";
        $member->setRoles($roles);


        //add susbcription
        $level = $levelRepository->findOneBy(['name' => $level]);
        $subscription = new Subscription(); 
        $subscription->setStudent($member);
        $subscription->setLevel($level);
        //$subject = $subjectRepository->findOneBy(['name' => $subject]);
        //$subscription->addSubject($subject);

        $months = 0;
        $priceReduction = 0;
        switch ($type_subscription) {
            case "Trimestre":
                $months = 3;
                $priceReduction = 1;
                break;
            case "Semestre":
                $months = 6;
                $priceReduction = 0.9;
                break;
            case "Année":
                $months = 12;
                $priceReduction = 0.8;
                break;
        }
        $startDate = new DateTime();
        $endDate = new DateTime();
        $endDate->setTimestamp($startDate->getTimestamp());
        $endDate->add(DateInterval::createFromDateString("+ " . $months . " months"));
        $subscription->setType($type_subscription);
        $subscription->setDateStart($startDate);
        $subscription->setDateEnd($endDate);
        $subscription->setIsActive(true);
        $totalPrice = $priceReduction * $months * 7;
        $subscription->setPrice(number_format($totalPrice, "2", ".", ""));

        //associate student to parent
        $tutor = $memberRepository->find($user_connected);        
        $kinship = new Kinship();
        $kinship->setStudent($member);
        $kinship->setTutor($tutor);

        $em->persist($kinship);
        $em->persist($subscription);
        $em->persist($member);
        $em->flush();


        return $this->json($tutor, 200, [], ['groups'=> 'post:read']);  

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
     * @Route("/api/subscription/student/check/{id}/{subject}", name="api_subscription_student_check", methods={"GET"})
     */
    public function parent_child_subscribe_check(Request $request, string $id, string $subject,  MemberRepository $memberRepository, EntityManagerInterface $em)
    {
        $query = $em->createQuery("SELECT sb FROM App\Entity\Subscription sb JOIN sb.student st JOIN sb.subjects subj WHERE sb.isActive=1 AND st.id=".$id." AND subj.id=".$subject."");
        $subscription = $query->getResult();

        if($subscription != null) {
                return $this->json($subscription, 200, [], ['groups'=> 'post:read']);                  
        } else {
            return $this->json([], 200, [], ['groups'=> 'post:read']);
        }
    }

    /**
     * @Route("/api/subscription/parent/student/unsubscribe/{id}/{subject}", name="api_subscription_student_unsubscribe", methods={"GET"})
     */
    public function parent_child_subscribe_unsubscribe(Request $request, string $id, string $subject,  MemberRepository $memberRepository, EntityManagerInterface $em, SubjectRepository $subjectRepository)
    {
        $query = $em->createQuery("SELECT sb FROM App\Entity\Subscription sb JOIN sb.student st JOIN sb.subjects subj WHERE sb.isActive=1 AND st.id=".$id." AND subj.id=".$subject."");
        $subscription = $query->getResult();
        $sub = $subscription[0];
        $subject = $subjectRepository->find($subject);
        $sub->setIsActive(false);
        $em->persist($sub);
        $em->flush();
        return $this->json($subscription, 200, [], ['groups'=> 'post:read']);                  
        
    }

    /**
     * @Route("/api/subscription/parent/student/done/{id}/{level}/{type_subscription}/{subject_id}", name="api_subscription_parent_student_done")
     */
    public function api_subscription_parent_student_done(Request $request, string $id, string $level, string $type_subscription, string $subject_id, EntityManagerInterface $em, MemberRepository $memberRepository, LevelRepository $levelRepository,  SubscriptionRepository $subscriptionRepository, SubjectRepository $subjectRepository)
    {
        
        $student = $memberRepository->find($id);
        
        //add susbcription
        $level = $levelRepository->findOneBy(['name' => $level]);
        $subscription = new Subscription(); 
        $subscription->setStudent($student);
        $subscription->setLevel($level);

        $months = 0;
        $priceReduction = 0;
        switch ($type_subscription) {
            case "Trimestre":
                $months = 3;
                $priceReduction = 1;
                break;
            case "Semestre":
                $months = 6;
                $priceReduction = 0.9;
                break;
            case "Année":
                $months = 12;
                $priceReduction = 0.8;
                break;
        }
        $startDate = new DateTime();
        $endDate = new DateTime();
        $endDate->setTimestamp($startDate->getTimestamp());
        $endDate->add(DateInterval::createFromDateString("+ " . $months . " months"));
        $subscription->setType($type_subscription);
        $subscription->setDateStart($startDate);
        $subscription->setDateEnd($endDate);
        $subscription->setIsActive(true);
        $totalPrice = $priceReduction * $months * 7;
        $subscription->setPrice(number_format($totalPrice, "2", ".", ""));

        $subject = $subjectRepository->find($subject_id);  
        $subscription->addSubject($subject);        
        $em->persist($subscription);
        $em->flush();
        return $this->json($subscription, 200, [], ['groups'=> 'post:read']);  
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
     * @Route("/api/payment/{id_user}/{owner_name}/{card_number}/{expiration_date}/{crypto}", name="api_payment")
     */
    public function api_payment(Request $request, int $id_user, string $owner_name, string $card_number, string $expiration_date, string $crypto, EntityManagerInterface $manager,  
                    MemberRepository $memberRepository)
    {

        $payment = new Payment();
        $user = $memberRepository->find($id_user);
        $payment->setUser($user);
        $payment->setOwnerName($owner_name);
        $payment->setCardNumber($card_number);
        $payment->setExpirationDate($expiration_date);
        $payment->setCrypto($crypto);
        $manager->persist($payment);
        $manager->flush();

        return $this->json($payment, 200, [], ['groups'=> 'post:read']);                  

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
        $query = $em->createQuery("SELECT tp FROM App\Entity\Topic tp JOIN tp.forum f JOIN f.level l WHERE l.id in (SELECT lv.id FROM App\Entity\Level lv JOIN lv.subscriptions sb JOIN sb.student s WHERE sb.isActive=1 AND s.id=".$id." GROUP BY lv.id)");
        //$query = $em->createQuery("SELECT his FROM App\Entity\Forum his JOIN his.actor a WHERE a.id=".$id."");
        $topics = $query->getResult();
        return $this->json($topics[0], 200, [], ['groups'=> 'post:read']);  
     }

    /**
     * @Route("/api/forum/{id}/posts", name="api_forum_posts")
     */
    public function api_forum_posts(Request $request, int $id, EntityManagerInterface $em, PostRepository $postRepository)
    {        
        $query = $em->createQuery("SELECT p FROM App\Entity\Post p JOIN p.topic t WHERE t.id=".$id."");
        //$query = $em->createQuery("SELECT  FROM App\Entity\Fohisrum his JOIN his.actor a WHERE a.id=".$id."");
        $posts = $query->getResult();
        return $this->json($posts, 200, [], ['groups'=> 'post:read']);  
    }

    /**
     * @Route("/api/forum/{id_topic}/posts/add/{id_author}/{content}", name="api_forum_posts_add")
     */
    public function api_forum_post_new(Request $request, int $id_topic, string $id_author, string $content, EntityManagerInterface $manager,  
                    MemberRepository $memberRepository, TopicRepository $topicRepository,  PostRepository $postRepository)
    {
        
        $author = $memberRepository->find($id_author);
        $topic = $topicRepository->find($id_topic);
        $post = new Post();
        $post->setAuthor($author);
        $post->setTopic($topic);
        $post->setContent($content);
        $post->setDate(new DateTime("now"));
        $manager->persist($post);
        $manager->flush();

        return $this->json($post, 200, [], ['groups'=> 'post:read']);                  

     }


      /*
     @Route("/api/subscription/list/parent/{id}", name="api_subscription_list_parent")
     
    public function api_subscription_list_parent(Request $request, int $id, EntityManagerInterface $em,  
    MemberRepository $memberRepository, SubscriptionRepository $subscriptionRepository, LevelRepository $levelRepository,  SubjectRepository $subjectRepository)
        {
        //$query = $em->createQuery("SELECT lv FROM App\Entity\Level lv  JOIN lv.courses c JOIN lv.subscriptions sb JOIN sb.student st  WHERE sb.isActive=1 AND st.id=".$id."");
        //$query = $em->createQuery("SELECT u FROM App\Entity\Member u JOIN u.kinshipStudents k JOIN k.student s JOIN s.subscriptions sb WHERE sb.isActive=1 AND u.id=".$user_connected_id."");
        //$query = $em->createQuery("SELECT k FROM App\Entity\Kinship sb JOIN sb.student s JOIN sb.subjects sj WHERE sb.isActive=1 AND s.id=".$id."");

        //$query = $em->createQuery("SELECT k.tutor FROM App\Entity\Kinship k JOIN k.tutor p WHERE p.id=".$id." GROUP BY k.id
        //                           SELECT u.id FROM App\Entity\Member u JOIN u.kinshipStudents k JOIN k.tutor p WHERE p.id=".$id." GROUP BY k.id");

        $query = $em->createQuery("SELECT sb FROM App\Entity\Subscription sb JOIN sb.student s WHERE sb.isActive=1 AND s.id=".$id."");

        $subscriptions = $query->getResult();

        return $this->json($subscriptions, 200, [], ['groups'=> 'post:read']);                  

        }
        */

    /**
     * @Route("/api/tutor/child/list/{user_connected_id}", name="api_tutor_child_list", methods={"GET"})
     */
    public function api_tutor_child_list(Request $request, string $user_connected_id, EntityManagerInterface $em, KinshipRepository $kinshipRepository, MemberRepository $memberRepository, CurrentMember $currentMember)
    {
        $tutor = $memberRepository->find($user_connected_id);
        
        //$query = $em->createQuery("SELECT u FROM App\Entity\Member u JOIN u.kinshipStudents k JOIN k.student s JOIN s.subscriptions sb WHERE sb.isActive=1 AND u.id=".$user_connected_id."");
        //$tutor = $query->getResult();

        if($tutor != null) {
            return $this->json($tutor, 200, [], ['groups'=> 'post:read']);                  
        } else {
            $tutor->setEmail("not found");
            return $this->json($tutor, 200, [], ['groups'=> 'post:read']);
        }
        
    }

     /**
     * @Route("/api/tutor/add/child/{email}/{user_connected_id}", name="api_tutor_add_child", methods={"GET"})
     */
    public function api_tutor_add_child(Request $request, string $email, string $user_connected_id, EntityManagerInterface $em, KinshipRepository $kinshipRepository, MemberRepository $memberRepository, CurrentMember $currentMember)
    {
        $student = $memberRepository->findOneBy(['email' => $email]);
        $tutor = $memberRepository->find($user_connected_id);

        if (!$student) {
            $tutor->setEmail("not found");
            return $this->json($tutor, 200, [], ['groups'=> 'post:read']);  
        } else if ($kinshipRepository->findOneBy(["student" => $student, "tutor" => $tutor])) {
            $tutor->setEmail("already your child");
            return $this->json($tutor, 200, [], ['groups'=> 'post:read']);  
        } else if ($kinshipRepository->findOneBy(["student" => $student])) {
            $tutor->setEmail("already associated");
            return $this->json($tutor, 200, [], ['groups'=> 'post:read']);  
            
        } else {
            $kinship = new Kinship();
            $kinship->setStudent($student);
            $kinship->setTutor($tutor);
            $em->persist($kinship);
            $em->flush();
            return $this->json($tutor, 200, [], ['groups'=> 'post:read']);  
        }
    }


    /**
     * @Route("/api/tutor/child/delete/{user_child_id}", name="api_tutor_child_delete", methods={"GET"})
     */
    public function api_tutor_child_delete(Request $request, string $user_child_id, EntityManagerInterface $em, KinshipRepository $kinshipRepository, MemberRepository $memberRepository)
    {
        
        $kinship = $kinshipRepository->find($user_child_id);
        $em->remove($kinship);
        $em->flush();
        
        $student = $memberRepository->find($kinship->getStudent()->getId());
        //var_dump($student->getFirstName()); die();
        $student->setIsActive(false);
        $em->persist($student);
        $em->flush();

        
        return $this->json($kinship, 200, [], ['groups'=> 'post:read']); 
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