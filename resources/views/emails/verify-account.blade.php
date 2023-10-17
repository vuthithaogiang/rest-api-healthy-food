
<h2>Thanks for register in our system <strong style="color:#b83a3a;">{{$user->name}}</strong></h2>
<p>To active account, please click in the bolow button</p>

<form action="http://127.0.0.1:8000/api/auth/verify-account/{{$user->verify_token}}" method="get" >
    <button style="border: none; background-color: #f1c351; color: #fff; padding: 12px 20px; cursor: pointer; font-size: 18px; margin-top: 20px; border-radius: 8px; " type="submit">Active Account</button>
</form>


