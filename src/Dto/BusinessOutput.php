<?php


namespace App\Dto;


use Symfony\Component\Serializer\Annotation\Groups;

class BusinessOutput
{
    /**
     * @Groups("read")
     */
    public $name;

    /**
     * @Groups("read")
     */

    public $image;

    /**
     * @Groups("read")
     */
    public $address;
     /**
     * @Groups("read")
     */

    public $owner;

    /**
     * @Groups("read")
     */
    public $id;


}
