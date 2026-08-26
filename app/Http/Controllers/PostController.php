<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PostController extends Controller
{
    //

    public function index($id){
        return "The parameter id: ". $id;
    }
}
