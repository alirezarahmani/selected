<?php


namespace App\Message;


class ShiftChangeNotification implements ParentMassenger
{
    private $userId;

    private $objectableIri;

    private $addShiftsInMail;


    public function getUserId()
    {
       return $this->userId;
    }

    public function getObjectableIri()
    {

    }
}
