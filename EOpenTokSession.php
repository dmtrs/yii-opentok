<?php
/**
 * EOpenTokSession
 **/
class EOpenTokSession extends CComponent
{
    /**
     * @var string id of the session
     **/
    private $_id;
    
    /**
     * @var string partner id of the session
     **/
    private $_partnerId;

    /**
     * @var string date of the session created
     **/
    private $_createdAt; 

    public function __construct($id, $partner=null, $createdAt=null)
    {
        $this->_id        = (string)$id;
        $this->_partnerId = (string)$partner;
        $this->_createdAt = (string)$createdAt;
    }

    public function getId()
    {
        return $this->_id;
    }
}
