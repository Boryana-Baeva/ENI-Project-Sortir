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
     * @var string
     */
    public $dateFilters = DateTime::class;

    /**
     * @var datetime
     */
    public $minDate;

    /**
     * @var datetime
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
    public $participants = false;




}