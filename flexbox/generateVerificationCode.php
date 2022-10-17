<?php 

$settings = AdminSettings::first();
$code = random_int(100000, 999999);

$user_id = auth()->user()->id;
$user = User::findOrFail($user_id);

$old_codes = VerifiedTransactions::where('user_id',$user_id)
    ->where('status','pending')
    ->get();

if (count($old_codes) != 0){
    foreach ($old_codes as $o){
        $o->status = 'expired';
        $o->save();
    }
}

$sql = new VerifiedTransactions;
$sql->code = $code;
$sql->user_id = $user_id;
$sql->created_at = Carbon::now();
$sql->expired_at = Carbon::now()->addMinute();
$sql->status = 'pending';
$sql->type = 1;
$sql->request_data = json_encode($this->request->current);


$_username = $user->username;
$_email_user = $user->email;
$_title_site = $settings->title;
$_email_noreply = $settings->email_no_reply;
App::setLocale($user->language == 1 ? 'en' : 'es');
if (env("APP_ENV") != "local") {
        Mail::send('emails.verify_code', ['verification_code' => $code,'username' => $_username, 'thisUrl' => getenv('APP_URL')],
            function ($message) use (
                $_username,
                $_email_user,
                $_title_site,
                $_email_noreply
            ) {
                $message->from($_email_noreply, $_title_site);
                $message->subject(trans('users.verification_code'));
                $message->to($_email_user, $_username);
            });
    }
$sql->save();
return response()->json([
    'success' => true
]);