<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

class UserController extends Controller
{
   public function index()
   {
    return response()->json([
        'message' => 'success',

    ]);
   }
   public function show($id)
   {
    return response()->json([
        'message' => 'success',
        'id' =>$id,

    ]);
   }
   public function destroy($id)
   {
    return response()->json([
        'message' => 'success',
        'id' =>$id,

    ]);
   }
   public function activate($id)
   {
    return response()->json([
        'message' => 'success',
        'id' =>$id,

    ]);
   }
}
 