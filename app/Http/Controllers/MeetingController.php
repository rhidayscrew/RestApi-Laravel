<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Meeting;

class MeetingController extends Controller
{

    //pertama kali di eksekusi oleh sistem
    //menggukanakan middleware, dan fiel nya ada vdi mildeware verifyJWTToken
        //memanggil jwt.auth :ada nya di kernel -  protected $routeMiddleware
    public function __construct()
    {

        $this->middleware(
            'jwt.auth',
            ['except' => ['index', 'show']]
        );
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $meetings = Meeting::all();

        //perulangan data
        foreach($meetings as $meeting){
            $meeting->view_meeting = [
                'href' => 'api/v1/meeting/' . $meeting->id,
                'method' => 'GET'
            ];
        }

        //membuat respon
        $response = [
            'message' =>'List of All Meetings',
            'meeting' => $meetings

        ];

        return response()->json($response, 200);
    }



    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //ini validasi, lebih jelas nya bisa lihat di documentasi nya
       //tulisan validasi nya bisa di ganti2 bisa langsung ke folder'resource-lang-validation' ini kan pake required
       $this ->validate($request, [
        'title' => 'required',
        'description' => 'required',
        'time' => 'required',
        'user_id' => 'required'
    ]);

    $title = $request -> input ('title');
    $description = $request -> input ('description');
    $time = $request -> input ('time');
    $user_id = $request -> input ('user_id');

    //membuat object meting
    $meeting = new Meeting([
        'time' => $time,
        'title' =>$title,
        'description' => $description
    ]);

    //membuat sebuah kondisi
    //documentasi Lqunr relationship
    if ($meeting->save()) {
        $meeting->users()->attach($user_id); //membuat data juga dgn attach di table pivot(meeting_User) nya dgn id user_id ygsama
        $meeting->view_meeting = [
         'href' => 'api/v1/meeting/' . $meeting->id,
         'method' => 'GET'
        ];
        $message = [
         'message' => 'Meeting Created',
         'meeting' => $meeting
        ];
          return response()->json($message, 201);
 }

 $response = [
     'message' => 'Error during creation'
 ];
 return response()->json($response, 404);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //documentasi Lqunr relationship
        $meeting = Meeting::with('users')->where('id', $id)->firstOrFail();
        $meeting->view_meetings = [
            'href' => 'api/v1/meeting',
            'method' => 'GET'
        ];

        $response = [
            'message' => 'Meeting Information',
            'meeting' => $meeting
        ];
        return response() ->json($response, 200);
    }



    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //ini validasi, lebih jelas nya bisa lihat di documentasi nya
       //tulisan validasi nya bisa di ganti2 bisa langsung ke folder'resource-lang-validation' ini kan pake required
       $this ->validate($request, [
        'title' => 'required',
        'description' => 'required',
        'time' => 'required',
        'user_id' => 'required'
    ]);

    $title = $request->input('title');
    $description = $request->input('description');
    $time = $request->input('time');
    $user_id = $request->input('user_id');

    $meeting = Meeting::with('users')->findOrFail($id);


//metode findOrFail : menunjukan ketikan id ini  tidak ada didalam database mka bukan hanya terjadi error dgn data null,
 //lanjutan nya:  ,tapi dia akan mengarahkan kita kehalaman not found yg sudah di handle oleh laravel
//menggunkana metode with
    if (!$meeting->users()->where('users.id', $user_id)->first()) {
        return response()->json(['message' => 'user not registered for meeting, update not successful'], 401);
    };
        //jika benar maka bisa di edit meeting nya
        $meeting-> time = $time;
        $meeting->title = $title;
        $meeting->description = $description;

        if(!$meeting->update()){
            //ada ingkaran(!) gagal di update maka rerspon nya ini
            return response()->json([
                'message' => 'Error during update'
            ], 404);
        }

        //jika bernar
        $meeting->view_meeting = [
            'href' => 'api/v1/meeting/' . $meeting->id,
            'method' => 'GET'
        ];

        $response = [
            'message' => 'Meeting Updated',
            'meeting' => $meeting
        ];

        return response()->json($response, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $meeting = Meeting::findOrFail($id);
        $users = $meeting->users;
        $meeting->users()->detach(); //menghaspu dulu di table pivot nya, baru menghapu di table meeting nya
                                      // detach : proses pelepasan data user yg ada di table pivot.
                                      // atach proses pembuatan data
                                      //part 16 RESTful API with Laravel 5.5 - 16 RESTful API CRUD Interaction Meeting part 2 time 15

        if(!$meeting->delete()){
            foreach ($users as $user){
                $meeting->users()->attach($user);
            }
            return response()->json([
                'message' => 'Deletion Failed'
            ], 404);
        }

        $response = [
            'message' => 'Meeting deleted',
            'create' => [
                'href' => 'api/v1/meeting',
                'method' => 'POST', //maksud dari metode POST ini yaitu metode apa yg kita gunakan pada saat kita membuat suatu meeting
                'params' => 'title, description, time' //dan parameter apa saja yg kita perlukan ketika membuat metode post tsb
            ]
        ];

        return response()->json($response, 200);
    }
}
