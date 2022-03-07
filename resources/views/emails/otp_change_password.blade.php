<p><b>Dear {{isset($data['name'])?$data['name']:'User'}},</b></p>
<p>
    Greetings from ACluster LLC.!
</p>

<p>    
    Your Change passoword veification code is <b>{{$data['otp']}}</b>    
</p>

<p>This otp will expire in {{isset($data['expires'])?$data['expires'].' seconds' :'few minutes'}}.</p>
