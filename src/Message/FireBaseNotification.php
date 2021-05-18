<?php


namespace App\Message;


class FireBaseNotification implements ParentMassenger
{
    private $Notification;

    private $content;

    private $user_id;

    private $objectable_iri;

    /**
     * @var string
     * shows the reason of this notification be scheduled
     */
    private $subject;

    /**
     * @return mixed
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param mixed $subject
     */
    public function setSubject($subject): void
    {
        $this->subject = $subject;
    }

    /**
     * @return mixed
     */
    public function getNotification()
    {
        return $this->Notification;
    }

    /**
     * @param mixed $Notification
     */
    public function setNotification($Notification): void
    {
        $this->Notification = $Notification;
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param mixed $content
     */
    public function setContent($content): void
    {
        $this->content = $content;
    }

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * @param mixed $user_id
     */
    public function setUserId($user_id): void
    {
        $this->user_id = $user_id;
    }

    /**
     * @return mixed
     */
    public function getObjectableIri()
    {
        return $this->objectable_iri;
    }

    /**
     * @param mixed $objectable_iri
     */
    public function setObjectableIri($objectable_iri): void
    {
        $this->objectable_iri = $objectable_iri;
    }


}
