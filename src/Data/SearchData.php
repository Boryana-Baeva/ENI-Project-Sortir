<?php


namespace App\Data;


use App\Entity\Campus;
use Symfony\Component\Validator\Constraints\DateTime;

class SearchData
{
    /**
     * @var string
     */
    public $q = '';

    /**
     * @var null|datetime
     */
    public $minDate;

    /**
     * @var null|datetime
     */
    public $maxDate;

    /**
     * @var boolean
     */
    public $organizer = false;

    /**
     * @var boolean
     */
    public $pastOutings = false;

    /**
     * @var boolean
     */
    public $subscribed;

    /**
     * @var boolean
     */
    public $unsubscribed;

    /**
     * @var Campus
     */
    public $campus;

}