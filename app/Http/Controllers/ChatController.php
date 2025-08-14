<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function index(){
    $users = User::limit(5)->get();
    return view('dashboad', ['users' => $users]);
    }
}
