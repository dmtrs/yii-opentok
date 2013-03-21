<?php
Yii::import('ext.yii-opentok.EOpenTokSession');
/**
 * EOpenTok
 **/
class EOpenTok extends CApplicationComponent
{
    /**
     * @var string api key
     **/
    public $key;

    /**
     * @var string api secret
     **/
    public $secret;

    /**
     * @var string url of the opentok service
     **/
    protected static $url = "http://api.opentok.com/hl";

    public function createSession($location='', $params=array())
    {
        $params['location'] = $location;
        $params['api_key']  = $this->key;

        $response = $this->post('/session/create', $params, $this->partnerAuth()); 
        $xml      = simplexml_load_string($response, 'SimpleXMLElement', LIBXML_NOCDATA);
        //TODO: proper handling of error and messages of the session creation
        return new EOpenTokSession($xml->Session->session_id, $xml->Session->partner_id, $xml->Session->create_dt);
    }

    //TODO:  most functionality must move to EOpenTokSession
    public function generateToken($session, $role=null, $expire=null, $data=null)
    {
        //TODO: constant this strings
        if($role===null || !in_array($role, array('publisher','moderator','subscriber')))
            $role = 'publisher';
        
        $data = array(
            'session_id'  => $session->id,
            'create_time' => time(),
            'role'        => $role,
            'nonce'       => microtime(true).mt_rand(),
        );
        //TODO: validate expire date against create time
        if($expire!==null) {
            $data['expire_time'] = $expire;
        }
        //TODO: if strlen of data > 1000 throw exception
        if($data!==null) {
            $data['connection_data'] = $data;
        }

        $data = http_build_query($data);
        $sig  = hash_hmac('sha1', $data, $this->secret);

        return 'T1=='.base64_encode(http_build_query(array(
            'partner_id' => $this->key,
            'sig'        => $sig,
        )).":{$data}");

    }

    protected function post($action, $params, $auth)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::$url.$action);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/x-www-form-urlencoded'));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array($auth));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        $res = curl_exec($ch);

        if(curl_errno($ch)) {
            throw new CException('Request error:'.curl_error($ch));
        }
        curl_close($ch);

        return $res;
    }

    /**
     * @return string Custom HTTP header
     **/
    protected function partnerAuth()
    {
        return "X-TB-PARTNER-AUTH: {$this->key}:{$this->secret}";
    }

    /**
     * @return string
     **/
    protected function tokenAuth($token)
    {
        return "X-TB-TOKEN-AUTH: {$token}";
    }
}
