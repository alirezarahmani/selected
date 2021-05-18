<?php

namespace App\Command;

use App\Entity\EmployeeAlert;
use App\Entity\Shift;
use App\Entity\User;
use App\Service\Notifier;
use App\Service\Timezone;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use function Doctrine\ORM\QueryBuilder;
use function Symfony\Component\DependencyInjection\Loader\Configurator\expr;

class ClockInReminderCommand extends Command
{
    protected static $defaultName = 'clockinreminder';
    /**
     * @var EntityManagerInterface
     */
    private $manager;
    /**
     * @var Timezone
     */
    private $timezone;
    /**
     * @var string|null
     */
    private $name;
    /**
     * @var Notifier
     */
    private $notifier;

    /**
     * ClockInReminderCommand constructor.
     * @param EntityManagerInterface $manager
     * @param Timezone $timezone
     * @param string|null $name
     * @param Notifier $notifier
     */
    public function __construct(EntityManagerInterface $manager,Timezone $timezone, Notifier $notifier,string $name = null)
    {
        parent::__construct($name);
        $this->manager = $manager;
        $this->timezone = $timezone;
        $this->name = $name;
        $this->notifier = $notifier;
    }

    protected function configure()
    {
        $this
            ->setDescription('this command call by a crontab to find user to notify therir shift')
            ->addArgument('time', InputArgument::REQUIRED, ' time to search for future shifts if time 5 e.g till up 5 minutes');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $qb=$this->manager->createQueryBuilder();
        $time=$input->getArgument('time');
        if (!is_numeric($time))
            $io->error('first argument should be number and specify minutes to search up next shift ');
        else{

            $now=date($this->timezone->getDefaultTimeFormat());

            $till_up=date($this->timezone->getDefaultTimeFormat(),strtotime($now." +".$time." minutes"));

            $shifts=$qb->select('sh')->from(Shift::class,'sh')->where(
                $qb->expr()->andX(
                    $qb->expr()->gte("sh.startTime","'".$now."'"),
                    $qb->expr()->lt("sh.startTime","'".$till_up."'"))
            )
                ->andWhere($qb->expr()->isNotNull('sh.ownerId'))
                ->getQuery()->getResult();

            $notif_counter=0;
            $email_counter=0;
            foreach ($shifts as $shift){
                /**
                 * @var User $user
                 */
                $user=$shift->getOwnerId();
                $tmp=$user->getEmployeeAlerts()->getValues();
                /**
                 * @var EmployeeAlert $emp_alert
                 */
                $emp_alert=$tmp[0];
                if ($emp_alert->getClockReminder()===EmployeeAlert::VALID_VALUES['mobile']){
                    $business=$shift->getScheduleId()->getBusinessId();
                    $res=$this->notifier->sendNotification($user,
                        Notifier::createMessage('clock in reminder','your shift start in 3 minutes'),
                        $shift,
                        $business);
                    $notif_counter++;
                }else{
                    $email_counter++;
                    $this->notifier->sendEmail($user,Notifier::createMessage('clock in reminder','your shift start in 3 minutes'),$shift);
                }
            }
            $io->success($email_counter." emails send and ".$notif_counter." notification send at ".$now);
            return 0;
        }

    }
}
