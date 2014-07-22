<?php

namespace Netgusto\BootCampBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerInterface,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response,
    Symfony\Component\HttpFoundation\RedirectResponse,
    Symfony\Component\Routing\Router,
    Symfony\Component\Form\FormFactory,
    Symfony\Component\Yaml\Yaml,
    Symfony\Component\Security\Core\Encoder\EncoderFactory,
    Twig_Environment;

use Doctrine\ORM\EntityManager,
    Doctrine\DBAL\Connection;

use Netgusto\BootCampBundle\Exception as BootCampException,
    Netgusto\BootCampBundle\Services as BootCampServices,
    Netgusto\BootCampBundle\Entity\AppUser,
    Netgusto\BootCampBundle\Form\Type as FormType,
    Netgusto\BootCampBundle\Entity\SystemStatus,
    Netgusto\BootCampBundle\Entity\HierarchicalConfig;

class InitializationController {

    protected $container;
    protected $twig;
    protected $environment;
    protected $urlgenerator;
    protected $formfactory;
    protected $em;
    protected $appversion;
    protected $passwordencoder_factory;
    protected $systemstatus;

    const DIAG_DBNOCONNECTION = 'DIAG_DBNOCONNECTION';
    const DIAG_DBMISSING = 'DIAG_DBMISSING';
    const DIAG_DBEMPTY = 'DIAG_DBEMPTY';
    const DIAG_APPNOTINITIALIZED = 'DIAG_APPNOTINITIALIZED';
    const DIAG_SYSTEMSTATUSMISSING = 'DIAG_SYSTEMSTATUSMISSING';
    const DIAG_CONFIGUREDVERSIONTOOHIGH = 'DIAG_CONFIGUREDVERSIONTOOHIGH';
    const DIAG_CONFIGUREDVERSIONTOOLOW = 'DIAG_CONFIGUREDVERSIONTOOLOW';
    const DIAG_OK = 'DIAG_OK';


    public function __construct(
        ContainerInterface $container,
        Twig_Environment $twig,
        BootCampServices\Context\EnvironmentService $environment,
        Router $urlgenerator,
        FormFactory $formfactory,
        EntityManager $em,
        $appversion,
        EncoderFactory $passwordencoder_factory,
        BootCampServices\Context\SystemStatusService $systemstatus
    ) {
        $this->twig = $twig;
        $this->environment = $environment;
        $this->urlgenerator = $urlgenerator;
        $this->formfactory = $formfactory;
        $this->em = $em;
        $this->appversion = $appversion;
        $this->passwordencoder_factory = $passwordencoder_factory;
        $this->systemstatus = $systemstatus;

        $this->appdiag = $this->appDiagnostic();

        # Disable the profiler
        if($container->has('profiler')) {
            $container->get('profiler')->disable();
        }
    }

    public function reactToExceptionAction(
        Request $request,
        BootCampException\InitializationNeeded\InitializationNeededExceptionInterface $e
    ) {
        
        if(($response = $this->ensureInitializationModeOn()) !== TRUE) {
            return $response;
        }

        if(strpos($request->attributes->get('_route'), '_init_') === 0) {
            
            # initialization in progress; just proceed with the requested controller
            return $this->proceedWithInitializationRequestAction(
                $request,
                $e
            );
        }

        switch(TRUE) {

            case $e instanceOf BootCampException\InitializationNeeded\InstallModeActivatedInitializationNeededException:
            case $e instanceOf BootCampException\InitializationNeeded\DatabaseMissingInitializationNeededException:
            case $e instanceOf BootCampException\InitializationNeeded\DatabaseEmptyInitializationNeededException: {
                $nextroute = '_init_welcome';
                break;
            }
            case $e instanceOf BootCampException\InitializationNeeded\SystemStatusMarkedAsUninitializedInitializationNeededException: {
                $nextroute = '_init_step2';
                break;
            }
            default: {
                die('unknownInitializationTaskAction');
            }
        }

        return new RedirectResponse($this->urlgenerator->generate($nextroute));
    }

    public function proceedWithInitializationRequestAction(
        Request $request,
        BootCampException\InitializationNeeded\InitializationNeededExceptionInterface $e
    ) {

        if(($response = $this->ensureInitializationModeOn()) !== TRUE) {
            return $response;
        }

        if($request->attributes->get('_route') === '_init_welcome') {
            return $this->welcomeAction($request);
        }

        if($request->attributes->get('_route') === '_init_step1_createdb') {
            return $this->step1CreateDbAction($request);
        }

        if($request->attributes->get('_route') === '_init_step1_createschema') {
            return $this->step1CreateSchemaAction($request);
        }

        if($request->attributes->get('_route') === '_init_step2') {
            return $this->step2Action($request);
        }

        if($request->attributes->get('_route') === '_init_finish') {
            return $this->finishAction($request);
        }

        if($request->attributes->get('_route') === '_init_dbnoconnection') {
            return new Response('<h2>No DB connection !</h2>');
        }
    }

    public function systemStatusMarkedAsUninitializedAction(Request $request, BootCampException\InitializationNeeded\SystemStatusMarkedAsUninitializedInitializationNeededException $e) {
        # System status exists, but marked as unitialized
        # It means that the initialization process has not passed step 2 yet

        if(($response = $this->ensureInitializationModeOn()) !== TRUE) {
            return $response;
        }
        
        return new RedirectResponse($this->urlgenerator->generate('_init_step2'));
    }

    protected function appDiagnostic() {

        $connection = $this->em->getConnection();

        # We check if the database exists
        try {
            $tables = $connection->getSchemaManager()->listTableNames();
        } catch(\PDOException $pdoexception) {
            if(strpos($pdoexception->getMessage(), 'Access denied') !== FALSE) {
                return self::DIAG_DBNOCONNECTION;
            } else {
                return self::DIAG_DBMISSING;
            }
        }

        if(empty($tables)) {
            return self::DIAG_DBEMPTY;
        }

        try {
            
            # SystemStatusMissingMaintenanceNeededException
            if($this->systemstatus->getInitialized() !== TRUE) {
                return self::DIAG_APPNOTINITIALIZED;
            }
        } catch(BootCampException\MaintenanceNeeded\SystemStatusMissingMaintenanceNeededException $e) {
            return self::DIAG_SYSTEMSTATUSMISSING;
        }

        $versiondiff = version_compare($this->systemstatus->getConfiguredversion(), $this->appversion);
        if($versiondiff > 0) {
            return self::DIAG_CONFIGUREDVERSIONTOOHIGH;
        } elseif ($versiondiff < 0) {
            return self::DIAG_CONFIGUREDVERSIONTOOLOW;
        }

        return self::DIAG_OK;
    }

    public function welcomeAction(Request $request) {

        if(($response = $this->ensureInitializationModeOn()) !== TRUE) {
            return $response;
        }

        if($this->appdiag === self::DIAG_OK) {
            return new RedirectResponse($this->urlgenerator->generate('_init_finish'));
        }

        switch($this->appdiag) {

            case self::DIAG_DBNOCONNECTION: {
                $nextroute = '_init_step1_dbnoconnection';
                break;
            }
            case self::DIAG_DBMISSING: {
                $nextroute = '_init_step1_createdb';
                break;
            }
            case self::DIAG_DBEMPTY: {
                $nextroute = '_init_step1_createschema';
                break;
            }
            case self::DIAG_APPNOTINITIALIZED:
            case self::DIAG_SYSTEMSTATUSMISSING:
            case self::DIAG_CONFIGUREDVERSIONTOOHIGH:
            case self::DIAG_CONFIGUREDVERSIONTOOLOW: {
                $nextroute = '_init_step2';
                break;
            }
            case self::DIAG_OK: {
                $nextroute = '_init_finish';
                break;
            }
        }

        return new Response($this->twig->render('NetgustoBootCampBundle:Initialization:welcome.html.twig', array(
            'nextroute' => $nextroute,
        )));
    }

    public function step1CreateDbAction(Request $request) {
        
        if(($response = $this->ensureInitializationModeOn()) !== TRUE) {
            return $response;
        }

        $form = $this->formfactory->create(new FormType\WelcomeStep1Type());
        $form->handleRequest($request);

        if($form->isValid()) {
            # The database is created and initialized
            $this->createDatabase($this->em->getConnection());
            $this->createSchema($this->em);
            $this->createSystemStatus($this->em, $this->appversion);
            $this->createSiteConfig($this->em, $this->environment);

            return new RedirectResponse($this->urlgenerator->generate('_init_step2'));
        }

        return new Response($this->twig->render('NetgustoBootCampBundle:Initialization:init_step1_createdb.html.twig', array(
            'form' => $form->createView(),
        )));
    }

    public function step1CreateSchemaAction(Request $request) {
        
        if(($response = $this->ensureInitializationModeOn()) !== TRUE) {
            return $response;
        }

        $form = $this->formfactory->create(new FormType\WelcomeStep1Type());
        $form->handleRequest($request);

        if($form->isValid()) {
            # The schemas are created
            $this->createSchema($this->em);
            $this->createSystemStatus($this->em, $this->appversion);
            $this->createSiteConfig($this->em, $this->environment);

            return new RedirectResponse($this->urlgenerator->generate('_init_step2'));
        }

        return new Response($this->twig->render('NetgustoBootCampBundle:Initialization:init_step1_createschema.html.twig', array(
            'form' => $form->createView(),
        )));
    }

    public function step2Action(Request $request) {
        
        if(($response = $this->ensureInitializationModeOn()) !== TRUE) {
            return $response;
        }

        $form = $this->formfactory->create(new FormType\WelcomeStep2Type());
        $form->handleRequest($request);
        if($form->isValid()) {

            $data = $form->getData();
            $user = new AppUser();

            $user->setEmail($data['email']);
            $user->setSalt(md5(rand() . microtime()));
            $user->setRoles(array('ROLE_ADMIN'));
            $user->setPassword(
                $this->passwordencoder_factory
                    ->getEncoder($user)
                    ->encodePassword(
                        $data['password'],
                        $user->getSalt()
                    )
            );

            $this->em->persist($user);
            $this->em->flush();

            # We mark the application as initialized
            $this->systemstatus->setInitialized(TRUE);

            return new RedirectResponse($this->urlgenerator->generate('_init_finish'));
        }

        return new Response($this->twig->render('NetgustoBootCampBundle:Initialization:init_step2.html.twig', array(
            'form' => $form->createView(),
        )));
    }

    public function finishAction(Request $request) {
        if(($response = $this->ensureInitializationModeOn()) !== TRUE) {
            return $response;
        }

        return new Response($this->twig->render('NetgustoBootCampBundle:Initialization:init_finish.html.twig'));
    }

    /* Utilitary functions */

    protected function ensureInitializationModeOn() {
        if($this->environment->getInitializationMode() !== TRUE) {
            
            if($this->appdiag === self::DIAG_OK) {
                return new RedirectResponse($this->urlgenerator->generate('home'));
            }

            return new Response('Initialization mode off. Access denied.', 401);
        }

        return TRUE;
    }

    protected function createDatabase(Connection $connection) {
        $databasecreator = new BootCampServices\Maintenance\DatabaseCreatorService();
        return $databasecreator->createDatabase($connection);
    }

    protected function createSchema(EntityManager $em) {
        $ormschemacreator = new BootCampServices\Maintenance\ORMSchemaCreatorService();
        return $ormschemacreator->createSchema($em);
    }

    protected function createSystemStatus(EntityManager $em, $appversion) {
        $systemStatus = new SystemStatus();
        $systemStatus->setConfiguredversion($appversion);
        $systemStatus->setInitialized(FALSE);

        $em->persist($systemStatus);
        $em->flush();
    }

    protected function createSiteConfig(EntityManager $em, BootCampServices\Context\EnvironmentService $environment) {

        #$configfile = $rootdir . '/data/config/config.yml';
        $configfile = $environment->getSrcdir() . '/Netgusto/BootCampBundle/Resources/config/config.yml.dist';

        $siteconfig = new HierarchicalConfig();
        $siteconfig->setName('config.site');
        $siteconfig->setConfig(
            Yaml::parse($configfile)
        );

        $em->persist($siteconfig);
        $em->flush();
    }
}