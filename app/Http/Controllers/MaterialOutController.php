<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\MaterialOut;
use App\Models\MaterialOutDetails;
use Auth;

class MaterialOutController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('material_outs.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validatedInput = $request->validate([
            'code' => 'nullable',
            'type' => 'nullable',
            'note' => 'required',
            'desc' => 'required'
        ]);



        $validatedInput['at'] = date('Y-m-d h:i:s');
        $validatedInput['last_updated_by_user_id'] = Auth::user()->id;
        $validatedInput['created_by_user_id'] = Auth::user()->id;
        $materialOut = MaterialOut::create($validatedInput);
        // dd($request->all());
        foreach($request->material_id as $row => $key){
            $materialOutDetails = new MaterialOutDetails();
            $materialOutDetails->material_out_id = $materialOut->id;
            $materialOutDetails->mat_in_detail_id = $request->material_id[$row];
            $materialOutDetails->qty = $request->qty[$row];
            $materialOutDetails->save();
        }

        return redirect(route('material_outs.index'))->with('message', [
          'class' => 'success',
          'text' => 'Berhasil menambah Material Output'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
        dd($request->all());
        $validatedInput = $request->validate([
            'code' => 'nullable',
            'last_updated_by_user_id' => 'required',
            'type' => 'required',
            'note' => 'required',
            'desc' => 'required'
        ]);

        $validatedInput['at'] = date('Y-m-d h:i:s');

        foreach($request->material_id as $row => $key){
            if ($materialInDetail = Material_in_details::find($request->idDetail[$row])) {
                $materialInDetail->material_id = $request->material_id[$row];
                $materialInDetail->qty = $request->qty[$row];
                $materialInDetail->price = $request->price[$row];
                $materialInDetail->update();
            } else {
                $materialInDetail = new Material_in_details();
                $materialInDetail->material_in_id = $id;
                $materialInDetail->material_id = $request->material_id[$row];
                $materialInDetail->qty = $request->qty[$row];
                $materialInDetail->price = $request->price[$row];
                $materialInDetail->save();
            }
        }

        return redirect(route('material_outs.index'))->with('message', [
          'class' => 'success',
          'text' => 'Berhasil Mengubah Material Outs'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
