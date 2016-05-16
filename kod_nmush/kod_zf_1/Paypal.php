<?php

class PayPal {

    /**
     * DB table name.
     * @var type 
     */
    public $paypalemail;     // e-mail продавца
    public $returnUrl;
    public $canselUrl;
    public $currency;               // валюта
    public $total;
    public $order_id;
    public $sign;
    public $item_name;
    public $custom;
    public $invoice;
    public $shipping;
    public $shipping2;

    function __construct($isSandbox) {
		if($isSandbox) {
			$this->paypalUrl = 'https://www.sandbox.paypal.com/cgi-bin/webscr';    //   sandbox
		} else {
			$this->paypalUrl = 'https://www.paypal.com/cgi-bin/webscr';          //  live
		}  
    }

    public function request($params) {
        $this->paypalemail = 'psho@mail.ru';
        $this->currency = 'USD';
        $this->returnUrl = 'http://' . $_SERVER['SERVER_NAME'] . '/mobilezone/';
        $this->canselUrl = 'http://' . $_SERVER['SERVER_NAME'] . '/mobilezone/site/fail';
        $this->total = (isset($params['Total']) && floatval($params['Total'])) ? $params['Total'] : 0;
        $this->shipping = (isset($params['shipping']) && floatval($params['shipping'])) ? $params['shipping'] : 0;
        $this->shipping2 = (isset($params['shipping2']) && $params['shipping2']) ? $params['shipping2'] : '';
        $this->order_id = (isset($params['order_id']) && intval($params['order_id'])) ? $params['order_id'] : 0;
        $this->sign = (isset($params['sign']) && intval($params['sign'])) ? $params['sign'] : 0;
        $this->item_name = (isset($params['item_name']) && $params['item_name']) ? $params['item_name'] : '';
        $this->custom = (isset($params['custom']) && $params['custom']) ? $params['custom'] : '';
        $this->invoice = (isset($params['invoice']) && $params['invoice']) ? $params['invoice'] : '';
        $baseUrl = Yii::app()->request->baseUrl;
              
        echo <<<FORM
<form method="post" action= "https://www.sandbox.paypal.com/cgi-bin/webscr" method="post">   
<input type="hidden" name="cmd" value="_xclick">
<input type="hidden" name="business" value="$this->paypalemail">
<input type="hidden" name="item_name" value="Payment for services">  
<input type="hidden" name="item_number" value="$this->order_id">
<input type="hidden" name="amount" value="$this->total"> 
<input type="hidden" name="shipping" value="$this->shipping">
<input type="hidden" name="shipping2" value="$this->shipping2">
<input type="hidden" name="no_shipping" value="0">
<input type="hidden" name="return" value="$this->returnUrl">
<input type="hidden" name="rm" value="2">
<input type="hidden" name="cancel_return" value="$this->canselUrl">
<input type="hidden" name="currency_code" value="$this->currency"> 
<input type="hidden" name="verify_sign" value="$this->sign">
<input type="hidden" name="custom" value="$this->custom" />
<input type="hidden" name="payer_business_name" value="">
<input type="hidden" name="invoice" value="$this->invoice"> 

<input type="hidden" name="item_name_x" value="mail_1">                      
<input type="image" src="$baseUrl/images/paypal_curved.png" style="width:48px; height:48px; border:none;" />
</FORM>
FORM;

        return 1;
    }

    /*   $post=$_POST   */

    public function response($post) {
        $email = '';
        $value = '';
        $status = 0;
        $request = "cmd=_notify-validate";
        $payedOrders = '<b>Payed with Paypal:<hr></b>';
        
        foreach ($_POST as $varname => $varvalue) {
            $email .= "$varname: $varvalue\n";
            if (function_exists('get_magic_quotes_gpc') and get_magic_quotes_gpc()) {
                $varvalue = urlencode(stripslashes($varvalue));
            } else {
                $value = urlencode($value);
            }
            $request .= "&$varname=$varvalue";
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->paypalUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);
        switch ($result) {
            case "VERIFIED":

                $payed = new Payed();
                $payed->orders = $post['invoice'];
                $payed->total = $post['mc_gross'];
                $payed->status = 1;
                $payed->save();
                $orderItem = preg_split('chukomuko/iUs', $post['invoice']);
                                
                foreach ($orderItem as $ord) {
                    if (trim($ord)) {
                        $uid = 0;
                       
                        $req = SimOrder::model()->findByAttributes(array('order' => $ord));
                        $uid=$req->user_id;
                        $user = User::model()->findByAttributes(array('id' => $uid));
                        if (count($req)) {
                            if ($ord) {
                                $req->status = 1;
                                $req->payedQuantity = intval($req->payedQuantity) + 1;
                                $req->save(false);
                            }
                           
                            $payedOrders = $payedOrders
                                    . '<b>Order:</b> ' . $ord . '<br>'
                                    . '<b>User:</b> ' . $user->username .' '.'<br>'
                                    . '<b>Email:</b> ' . $user->email . '<br>'
                                    . '<b>Sim: </b>' . $req->sim->sim_name . '<br>'
                                    . '<b>Date start:</b> ' . $req->dateBegin . '<br>'
                                    . '<b>Date end:</b> ' . $req->dateEnd . '<br>'                                    
                            ;
                            
                            if($req->blackberry){ 
                                $blackberry= Phones::model()->findByAttributes(array('alias' => 'Blackberry'));
                                $blackberry->phone_price;  
                                $payedOrders = $payedOrders."<b>blackberry:</b> {$blackberry->phone_price}<br>";
                            }
                            
                            if($req->interCalling){
                                $payedOrders = $payedOrders."<b>International calling:</b> {$req->interCalling}<br>";
                            }
                            
                            if($req->keep_number){
                                $payedOrders = $payedOrders."<b>Keep number (1 year):</b> {$req->sim->coast_peryear}<br>";
                            }                           
                            
                        }
                        Tempsims::model()->deleteAll('`order` =:order', array('order' => $ord));
                        unset(Yii::app()->session['myorders']);
                        unset(Yii::app()->session['redirAfterLogin']);
                        if (isset(Yii::app()->session['myorders'])) {
                            Yii::app()->session['myorders'] = '';
                        }
                        if (isset(Yii::app()->session['redirAfterLogin'])) {
                            Yii::app()->session['redirAfterLogin'] = '';
                        }
                    }
                }

                /*     send email   */
                $to = User::model()->findByPk($req->id);
                $message = new YiiMailMessage;
                $params = $payedOrders;
                $message->subject = 'Paypal payment';
                $message->setBody($params, 'text/html');
                $message->addTo('davidarm@gmail.com');
                $message->addTo('armjer@mail.ru');
                $message->from = 'info@mobilezone.am';
                if (Yii::app()->mail->send($message)) {
                    echo 'success!';
                }

                $status = 1;
                break;
            case "INVALID":
                // ошибка
                $status = 0;
                break;
            default:
            // в других случаях
        }
        return $status;
    }

    public function responseForUpdate($post) {
        $email = '';
        $value = '';
        $status = 0;
        $request = "cmd=_notify-validate";

        foreach ($_POST as $varname => $varvalue) {
            $email .= "$varname: $varvalue\n";
            if (function_exists('get_magic_quotes_gpc') and get_magic_quotes_gpc()) {
                $varvalue = urlencode(stripslashes($varvalue));
            } else {
                $value = urlencode($value);
            }
            $request .= "&$varname=$varvalue";
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->paypalUrl);                           
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);
        switch ($result) {
            case "VERIFIED":

                $payed = new Payed();
                $payed->orders = $post['invoice'];
                $payed->total = $post['mc_gross'];
                $payed->status = 1;
                $payed->save();
                $orderItem = preg_split('/chukomuko/iUs', $post['invoice']);
                // echo $post['invoice'];
                print_r($orderItem);

                $activate = new activatephone();
                $login = $activate->login();
                $loginStatus = json_decode($login);

                if (intval($loginStatus->{'status'}) == 0 || intval($loginStatus->{'status'}) == 4) {
                    foreach ($orderItem as $ord) {
                        $number = preg_replace('/^\w*chukomuko/iUs', '', $ord);
                        $ord = preg_replace('/chukomuko\w*$/iUs', '', $ord);

                        $req = SimOrder::model()->findByAttributes(array('order' => $ord));
                        if (count($req)) {
                            if ($ord) {
                                $res = 0;
                                $req->payedQuantity = intval($req->payedQuantity) + 1;
                                $res = $req->save();
                                if ($res) {
                                    echo 'activating<br>';
                                    $activated = $activate->activate($number, $req->dateBegin, $req->dateEnd);
                                    $activatedStatus = json_decode($activated);
                                    $stat = intval($activatedStatus->{'status'});
                                    if ($stat == 0) {
                                        $a1 = OrderSimnumber::model()->findByAttributes(array('phone_num' => $number));
                                        $a1['payed'] = 1;
                                        $a1['status'] = 2;
                                        $a1->save();
                                    } else {
                                        $a1 = OrderSimnumber::model()->findByAttributes(array('phone_num' => $number));
                                        $a1['payed'] = 1;
                                        $a1['status'] = 0;
                                        $a1->save();
                                    }
                                }
                            }

                            /*  если все номера с таким ордером уже обработаны, то status=2;       */
                            $req1 = SimOrder::model()->findByAttributes(array('order' => $ord));
                            if (count($req1)) {
                                if (intval($req1->payedQuantity) >= intval($req1->cartQuantity)) {
                                    $req1->status = 2;
                                    $req1->save();
                                }
                            }
                        }
                    }
                }

                if (Yii::app()->session["data_update"]) {
                    unset(Yii::app()->session['data_update']);
                }


                $status = 1;
                break;
            case "INVALID":
                // ошибка
                $status = 0;
                break;
            default:
            // в других случаях
        }
        return $status;
    }

}
