<?php


namespace App\Controller\MailToSend;


use App\Entity\MailsToSend;
use Doctrine\ORM\EntityManagerInterface;
use Twig\Environment;
use function Doctrine\ORM\QueryBuilder;

class SendEmail
{
    /**
     * @var EntityManagerInterface
     */
    private $manager;
    /**
     * @var \Swift_Mailer
     */
    private $mailer;
    /**
     * @var Environment
     */
    private $templating;

    public function __construct(EntityManagerInterface $manager, \Swift_Mailer $mailer,Environment $templating)
    {
        $this->manager = $manager;
        $this->mailer = $mailer;
        $this->templating = $templating;
    }

    public function __invoke()
    {





    }


}
