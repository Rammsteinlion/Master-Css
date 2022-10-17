<?php


//dd($this->request->current);

$query = VerifiedTransactions::where('user_id', auth()->user()->id)
->where('status','pending')
->first();

if($query){
if($query->code == $this->request->code){
if($query->expired_at >= Carbon::now()){
$query->status = 'verified';
$query->save();
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