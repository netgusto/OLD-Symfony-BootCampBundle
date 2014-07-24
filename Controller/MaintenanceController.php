<?php

namespace Netgusto\BootCampBundle\Controller;

use Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response,
    Twig_Environment;

use Netgusto\BootCampBundle\Exception as BootCampException,
    Netgusto\BootCampBundle\Services as BootCampServices;

class MaintenanceController {

    protected $twig;

    public function __construct(Twig_Environment $twig) {
        $this->twig = $twig;
    }

    public function reactToExceptionAction(Request $request, BootCampException\MaintenanceNeeded\MaintenanceNeededExceptionInterface $e) {

        /*
            Maintenance actions are not yet implemented;
            TODO: Implement maintenance actions and map them here
        */
    }

    public function proceedWithRequestAction(Request $request, BootCampException\MaintenanceNeeded\MaintenanceNeededExceptionInterface $e) {
        /*
            Maintenance routes are not yet defined;
            TODO: Implement maintenance routes and map them here
        */
    }

    public function databaseInvalidCredentialsAction(Request $request, BootCampException\MaintenanceNeeded\DatabaseInvalidCredentialsMaintenanceNeededException $e) {
        return new Response($this->twig->render('NetgustoBootCampBundle:Maintenance:databaseinvalidcredentials.html.twig'));
    }

    public function databaseUpdateAction(Request $request, BootCampException\MaintenanceNeeded\DatabaseUpdateMaintenanceNeededException $e) {
        return new Response($this->twig->render('NetgustoBootCampBundle:Maintenance:databaseupdate.html.twig'));
    }

    public function administrativeAccountMissingAction(Request $request, BootCampException\MaintenanceNeeded\AdministrativeAccountMissingMaintenanceNeededException $e) {
        return new Response($this->twig->render('NetgustoBootCampBundle:Maintenance:administrativeaccountmissing.html.twig'));
    }

    public function systemStatusMissingAction(Request $request, BootCampException\MaintenanceNeeded\SystemStatusMissingMaintenanceNeededException $e) {
        return new Response($this->twig->render('NetgustoBootCampBundle:Maintenance:systemstatusmissing.html.twig'));
    }

    public function siteConfigFileMissingAction(Request $request, BootCampException\MaintenanceNeeded\SiteConfigFileMissingMaintenanceNeededException $e) {
        return new Response($this->twig->render('NetgustoBootCampBundle:Maintenance:siteconfigfilemissing.html.twig'));
    }

    public function unknownMaintenanceTaskAction(Request $request, BootCampException\MaintenanceNeeded\MaintenanceNeededExceptionInterface $e) {
        return new Response($this->twig->render('NetgustoBootCampBundle:Maintenance:unknownmaintenancetask.html.twig'));
    }
}