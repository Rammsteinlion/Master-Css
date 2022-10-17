<?php

if($query){
    if($query->code == $this->request->code){
        if ($query->expired_at > Carbon::now()->minute){
            $query->status = 'verified';
            $query->save();

            $request = json_decode($query->request_data);
            //$request = preg_replace( '([^A-za-z09])','',$query->request_data);

            dd($request);
            if($query->type==1) {

                if ($request->type != 'paypal' && $request->type != 'bank' && $request->type != 'coinpayments' && $request->type != 'astropay' && $request->type != 'cosmopayments') {
                    return response()->json([
                        'success' => false,
                    ], 400);

                }

                // Validate Email Paypal
                if ($request->type == 'paypal') {
                    $rules = array(
                        'email_paypal' => 'required|email|confirmed',
                    );

                    //$this$this->validate($request, $rules);

                    $user = User::find(auth()->user()->id);
                    $user->paypal_account = $request->email_paypal;
                    $user->payment_gateway = 'PayPal';
                    $user->save();


                }// Validate Email Paypal

                elseif ($request->type == 'bank') {
                    $rules = array();
                    //id del banco en la DB
                    /*$idbank = User::select('id','payment_gateway','bank','countries_id' )->find(auth()->user()->id);
                    $idbankuser = json_decode($idbank);*/

                    $countriesiduser = auth()->user()->countries_id;
                    $idbankname = $request->bank_name;
                    $account_number = $request->account_number;

                    settype($idbankname, "integer");
                    $sql = Banks_countries::where('country_id', '=', $countriesiduser)->get();
                    $bankNme = json_decode($sql);

                    $arrB = array();
                    foreach ($bankNme as $dato) {
                        $arr = array();
                        $arr['id'] = $dato->id;
                        $arr['bank'] = $dato->bank;
                        array_push($arrB, $arr);
                    }

                    $B = $arrB;
                    for ($i = 0; $i < sizeof($B); $i++) {
                        $datos = $B[$i];
                        $id = $datos['id'];
                        if ($id == $idbankname) {
                            $banco = $datos['bank'];
                        }
                    }

                    $array = array(
                        'bank_details' => $request->bank_details,
                        'account_number' => $request->account_number,
                        'swift_code' => $request->swift_code,
                        'document' => $request->document,
                        'bank_name' => !isset($banco) ? $request->bank_name : $banco
                        //'bank_name' => auth()->user()->payment_gateway . '  '. $idbankuser->account_number
                    );


                    //$this->validate($request, $rules);

                    //id del banco en la DB
                    /*$idbank = User::select('id','payment_gateway','bank','countries_id' )->find(auth()->user()->id);
                    $idbankuser = json_decode($idbank->bank);

                    //id del banco de la DB
                    $IdBanco = Banks_countries::select('id','country_id','bank')->where("country_id", "=",  auth()->user()->countries_id)->get();
                    //$Bank = json_decode($IdBanco);
                    $Bank = $IdBanco;

                    //ID del banco en el form
                    $bank_name = $request->bank_name;

                    $arrB = Array();
                    foreach ($Bank as $dato){
                        $arr= array();
                        $arr['id'] = $dato->id;
                        $arr['bank'] = $dato->bank;
                        array_push($arrB,$arr);
                    }
                    $B = $arrB;

                    for($i=0; $i < sizeof($B); $i++){
                        $datos = $B[$i];
                        $id = $datos['id'];
                        if($id == $bank_name){
                            $bank = $datos['bank'];
                        }
                    }*/


                    $user = User::find(auth()->user()->id);
                    $user->bank = strip_tags(json_encode($array));
                    $user->payment_gateway = 'BankTransfer';
                    $user->account_number = $account_number;
                    $user->save();


                } elseif ($request->type == 'coinpayments') {

                    $rules = array(
                        'address_coinpayments' => 'required|min:5',
                        'currency' => 'required|min:2',
                        'coinpayments_net' => 'required|min:5',
                    );

                    //$this->validate($request, $rules);


                    $user = User::find(auth()->user()->id);
                    $user->coinpayments_wallet = ($request->address_coinpayments);
                    $user->coinpayments_currency = $request->currency;
                    $user->coinpayments_net = $request->coinpayments_net;
                    $user->payment_gateway = 'coinpayments';
                    $user->save();


                } elseif ($request->type == 'astropay') {

                    $rules = array(
                        'astropay_phone_number' => 'required',
                    );

                    //$this->validate($request, $rules);

                    $user = User::find(auth()->user()->id);
                    $user->astropay_number = $request->astropay_phone_number;
                    $user->payment_gateway = 'AstropayCard';
                    $user->save();


                } elseif ($request->type == 'cosmopayments') {

                    $rules = array(
                        'cosmopayments_number' => 'required',
                    );

                    //$this->validate($request, $rules);
                    $user = User::find(auth()->user()->id);
                    $user->cosmopayments_number = $request->cosmopayments_number;
                    $user->payment_gateway = 'Cosmopayments';
                    $user->save();
                }
            }

            return response()->json([
                'success' => true
            ]);
        }else{
            $query->status = 'expired';
            $query->save();
            return response()->json([
                'success' => false,
                'message' => 'code_verification.expired'
            ]);
        }
    }else{
        return response()->json([
            'success' => false,
            'message' => 'code_verification.incorrect'
        ]);
    }
}else{
    return response()->json([
        'success' => false,
        'message' => 'code_verification.not_code_sent'
    ]);
}