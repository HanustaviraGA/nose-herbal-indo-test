<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    public function index(){
        return view('index');
    }

    /**
     * Load a list of data using Datatables SSP.
     * @param Request $request
    */
    public function init_table(){
        $query = User::all();
        return select_table($query);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     */
    public function create(Request $request)
    {
        $data = $request->all();
        \DB::beginTransaction();
        try {
            // Validation
            $validate = $request->validate([
                'name' => 'required',
                'email' => 'required|email',
                'password' => 'required',
            ]);
            if(trim($data['email']) == ''){
                return response()->json(['success' => false, 'message' => 'Email harus diisi'], 500);
            }
            if(trim($data['name']) == ''){
                return response()->json(['success' => false, 'message' => 'Nama harus diisi'], 500);
            }
            if(trim($data['password']) == ''){
                return response()->json(['success' => false, 'message' => 'Kata Sandi harus diisi'], 500);
            }
            // Checking
            $check = User::where('email', $data['email'])->count();
            if($check){
                return response()->json(['success' => false, 'message' => 'Email telah terdaftar'], 500);
            }
            $data['id'] = md5(rand(0, 100).generateCode());
            $data['password'] = bcrypt($data['password']);
            $query = User::create($data);
            \DB::commit();
            return response()->json(['success' => true], 200);
        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 401);
        }
    }

    /**
     * Show the specified resource.
     * @param int $id
     */
    public function read(Request $request)
    {
        $data = $request->all();
        $query = User::where('id', $data['id'])->first();
        if($query){
            return response()->json(['success' => true, 'data' => $query], 200);
        }else{
            return response()->json(['success' => false], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     */
    public function update(Request $request)
    {
        $data = $request->all();
        if($data['password'] == ''){
            unset($data['password']);
        }
        \DB::beginTransaction();
        try {
            // Validation
            $validate = $request->validate([
                'name' => 'required',
                'email' => 'required|email',
            ]);
            if(trim($data['email']) == ''){
                return response()->json(['success' => false, 'message' => 'Email harus diisi'], 500);
            }
            if(trim($data['name']) == ''){
                return response()->json(['success' => false, 'message' => 'Nama harus diisi'], 500);
            }
            $query = User::where('id', $data['id'])->update($data);
            \DB::commit();
            return response()->json(['success' => true], 200);
        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 401);
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     */
    public function delete(Request $request)
    {
        $data = $request->all();
        \DB::beginTransaction();
        try {
            $query = User::where('id', $data['id'])->delete();
            \DB::commit();
            return response()->json(['success' => true], 200);
        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 401);
        }
    }
}
