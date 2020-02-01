<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class HomeController extends Controller
{
    public function index()
    {
        dd(urldecode(
            "https://vuetify-demo1.test:3000/auth/callback?#access_token=EAADhZCZCFgDnoBAJ15iuZCDcCNx83efn7KuqbrTYu06S3heZAoyrWjdtL3QHdSxwJYWVoZAADBgIxRGOl08rEcsY3wZB2aCFFK8PWQwB9giNqev7uhhJPNHREKTrJ9k7MW7wqB9H8JEFlKZAxfe1TWP2akm6insZA4ItNzubqlwRFuHTDlgD7x7SgXkArZA6hZB7YZD&data_access_expiration_time=1574947445&expires_in=5755&state=7ITtSOVQAGkLb3an7En9y"
        ));
        return 'Welcome to Bengali Connect API';
    }
}
