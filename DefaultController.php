<?php

namespace App\AcmeTestBundle\Controller;

use App\Service\VisitCounter;
use Predis\Client;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Class DefaultController
 * @var VisitCounter $visitCounterService
 * @package App\AcmeTestBundle\Controller
 */
class DefaultController extends AbstractController
{
    private $visitCounterService;

    /**
     * DefaultController constructor.
     * @param VisitCounter $visitCounterService
     */
    public function __construct(VisitCounter $visitCounterService)
    {
        $this->visitCounterService = $visitCounterService;
    }

    public function logIn()
    {
        //some logic...

        $this->visitCounterService->logIn();
    }

    public function logOff()
    {
        //some logic...

        $this->visitCounterService->logOff();

        // return $this->redirect('acme_test_index');
    }

    /**
     * @param Client $redis
     * @param VisitCounter $visitCounterService
     * @throws \Exception
     */
    public function index(Client $redis, VisitCounter $visitCounterService)
    {
        $this->logIn();
    }

    /**
     * Statistic by visitors
     *
     * @param Client $redis
     * @param VisitCounter $visitCounterService
     * @throws \Exception
     */
    public function statVisitor(Client $redis, VisitCounter $visitCounterService)
    {
        $counts = $visitCounterService->getStat();
    }
}

