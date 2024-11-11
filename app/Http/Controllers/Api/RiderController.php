<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RiderResource;
use App\Models\Rider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class RiderController extends Controller
{
    public function index()
    {
        $rider = Rider::latest()->paginate(5);

        return new RiderResource(true, 'List Kamen Rider', $rider);
    }

    public function store(Request $request)
    {
        // devine validator
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'name' => 'required',
            'series' => 'required',
            'user' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // upload
        $image = $request->file('image');
        $image->storeAs('public/riders', $image->hashName());

        $rider = Rider::create([
            'image' => $image->hashName(),
            'name' => $request->name,
            'series' => $request->series,
            'user' => $request->user
        ]);

        return new RiderResource(true, 'Data Rider Berhasil Ditambahkan!', $rider);
    }

    public function show($id)
    {
        // find id
        $rider = Rider::find($id);

        return new RiderResource(true, 'Data Rider Ditemukan!', $rider);
    }

    public function update(Request $request, $id)
    {
        // validator
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'series' => 'required',
            'user' => 'required'
        ]);

        // jika fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $rider = Rider::find($id);

        if ($request->hasFile('image')) {
            // upload
            $image = $request->file('image');
            $image->storeAs('public/riders', $image->hashName());

            // delete image lama
            Storage::delete('public/riders/' . basename($rider->image));

            // update
            $rider->update([
                'image' => $image->hashName(),
                'name' => $request->name,
                'series' => $request->series,
                'user' => $request->user
            ]);
        } else {
            // update without image
            $rider->update([
                'name' => $request->name,
                'series' => $request->series,
                'user' => $request->user
            ]);
        }

        return new RiderResource(true, 'Data Rider Berhasil Diupdate!', $rider);
    }

    public function destroy($id)
    {
        $rider = Rider::find($id);

        // delete image
        Storage::delete('public/riders/' . basename($rider->image));

        // delete
        $rider->delete();

        return new RiderResource(true, 'Data Rider Berhasil Dihapus!', null);
    }
}
