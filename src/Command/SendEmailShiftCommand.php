<?php

namespace App\Command;

use App\Entity\MailsToSend;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Twig\Environment;

class SendEmailShiftCommand extends Command
{
    protected static $defaultName = 'send_email_shift';
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


    public function __construct(EntityManagerInterface $manager,\Swift_Mailer $mailer,Environment $templating)
    {
        parent::__construct();
        $this->manager = $manager;
        $this->mailer = $mailer;
        $this->templating = $templating;
    }

    protected function configure()
    {
        $this
            ->setDescription('send emails from mails_to_send for user to inform from shift changes')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $qb=$this->manager->createQueryBuilder();
        $qb->select('mails')
            ->from(MailsToSend::class,'mails')
            ->where(sprintf("mails.status='%s'",MailsToSend::STATUS[0]))->setMaxResults(1);
        //each time fetch one mails and send them

        $mails=$qb->getQuery()->getResult();
        if (!is_null($mails) && count($mails)>0){
            /**
             * @var MailsToSend $mail
             */
            $mail=$mails[0];
            $message = (new \Swift_Message('shift changes'))
                ->setFrom('support@studyfirstgroup.com','selectedTime')
                ->setTo($mail->getReceiverEmail())
                ->setBody(
                    $this->templating->render(
                    // templates/emails/registration.html.twig
                        'emails/change_shift.html.twig',
                        ['name' => $mail->getReceiverFirstName().' '.$mail->getReceiverLastName(),'shifts'=>$mail->getShiftsInMail()]
                    ),
                    'text/html'
                )->addPart(
                    $this->templating->render(
                    // templates/emails/registration.txt.twig
                        'emails/change_shift.txt.twig',
                        ['name' => $mail->getReceiverFirstName().' '.$mail->getReceiverLastName(),'shifts'=>$mail->getShiftsInMail()]
                    ),
                    'text/plain'
                );
            $num=$this->mailer->send($message);
            if ($num>0){
                $mail->setStatus(MailsToSend::STATUS[2]);
                $this->manager->persist($mail);
                $this->manager->flush();
                $io->success('emails send successfully');
            }else{
                $io->error('no email send');
            }

        }

        $io->success('everything is up to date');


    }
}
