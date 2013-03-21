<?php
/**
 * EOpenTokWidget
 **/
class EOpenTokWidget extends CWidget
{
    public $key;
    public $sessionId;
    public $token;

    public $webRTCEnabled = true;

    public function init()
    {
        Yii::app()->clientScript->registerScriptFile($this->scriptUrl(), CClientScript::POS_HEAD);
        echo CHtml::tag('div', array('id'=>$this->id),'');
        Yii::app()->clientScript->registerScript(__CLASS__.":".$this->id, <<<EOF
var apiKey    = "{$this->key}";
var sessionId = "{$this->sessionId}";
var token     = "{$this->token}";

// Initialize session, set up event listeners, and connect
var session = TB.initSession(sessionId);
session.addEventListener('sessionConnected', sessionConnectedHandler);
session.connect(apiKey, token);

function sessionConnectedHandler(event) {
var publisher = TB.initPublisher(apiKey, '{$this->id}');
session.publish(publisher);
}
EOF
);
    }

    private function scriptUrl()
    {
        return ($this->webRTCEnabled) ? 'https://swww.tokbox.com/webrtc/v2.0/js/TB.min.js' : 'https://swww.tokbox.com/v1.1/js/TB.min.js';
    }
}
