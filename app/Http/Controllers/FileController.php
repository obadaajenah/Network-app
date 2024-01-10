<?php

namespace App\Http\Controllers;

use App\Models\UserFile;
use App\Models\File;
use App\Models\Reservation;
use Illuminate\Http\Request;
// use Illuminate\Support\Facuse ;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File as FacadesFile;
use App\Http\Requests\FileRequest;
use Illuminate\Support\Str;
use App\Aspects\logger;
use App\Models\reports;
use Illuminate\Contracts\Cache\Store;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{


    public function all_files()
    {
        $files = File::where('status','free')->get();
        return response()->json([
            'status' => 200,
            'files' =>$files
        ],200);
    }


public function store(FileRequest $request)
{
    try {
        DB::beginTransaction();

        // Store the uploaded file
        $uploadedFile = $request->file('file');
        $path = $uploadedFile->store('uploads', 'public');

        // Create a file record
        $file = File::create([
            'name' => $uploadedFile->getClientOriginalName(),
            'status' => 'free',
            'path' => $path,
            'user_id' => Auth::guard('user-api')->id(),
        ]);


        Reports::create([
            'file_id' => $file->id,
            'event_type' => 'uploaded',
            'event_date' => now(),
            'user_id' => $file->user_id,
        ]);

        DB::commit();

        return response()->json(['message' => 'created successfully']);
    } catch (\Exception $ex) {
        DB::rollBack();
        return response()->json(['message' => 'something went wrong: ' . $ex->getMessage()], 500);
    }
}






    // public function download($id)
    // {
    //     // try {
    //         // DB::beginTransaction();

    //         $file = File::lockForUpdate()->find($id);

    //         if (!$file) {
    //             return response()->json(['message' => 'File not found.'], 404);
    //         }

    //         if ($file->status != 'free') {
    //             return response()->json(['message' => 'File is not available for download.'], 400);
    //         }

    //         $user = auth::guard('user-api')->id();
    //         $file->status = 'taken';
    //         $reservedFile = Reservation::create([
    //             'user_id' => Auth::guard('user-api')->id(),
    //             'file_id' => $file->id,
    //             'type' => 'reserved',
    //         ]);
    //         $file->save();

    //         // DB::commit();

    //         $path = Str::after(urlencode($file->name), 'storage/');
    //         $filePath = storage_path('app\uploads/' . $path);

    //         // if (!file_exists($filePath)) {
    //         //     return response()->json(['message' => 'File not found.'], 404);
    //         // }
    //         return Storage::download($filePath, $file->name);


    //     // }
    //     // catch (\Exception $ex) {
    //     //     DB::rollBack();
    //     //     return response()->json(['message' => $ex->getMessage()], 500);
    //     //  }

    // }

    #[logger]
    public function download($id)
{
    try {


        //  // Validate the number and size of uploaded files
        //  request()->validate([
        //     'files.*' => 'required|file|max:2048', // Adjust the max size as needed
        //     'files' => 'max:5', // Adjust the max number of files as needed
        // ]);

        $file = File::lockForUpdate()->find($id);
        DB::beginTransaction();

        if (!$file) {
            return response()->json(['message' => 'File not found.'], 404);
        }

        if ($file->status != 'free') {
            return response()->json(['message' => 'File is not available for download.'], 400);
        }


        $user = auth()->guard('user-api')->user();
        $file->status = 'taken';
        $reservedFile = Reservation::create([
            'user_id' => $user->id,
            'file_id' => $file->id,
            'type' => 'reserved',
        ]);
        $file->save();
        Reports::create([
            'file_id' => $file->id,
            'event_type' => 'download',
            'event_date' => now(),
            'user_id' => $user->id,
        ]);
        DB::commit();

        $filePath = $file->path;
        return response()->json(['The path to download'=>'storage/'.$file->path,$file->name]);


    } catch (\Exception $ex) {

        return response()->json(['message' => $ex->getMessage()], 500);
    }
}

    public function destroy($id){
        $file = File::lockForUpdate()->findOrFail($id);
        if($file->status != 'free')
            return  response()->json(['message'=>'you cant delete file cause is taken'],403);
            DB::table('files')->where('id',$id)->delete();
            $file->delete();

        return response()->json(['message'=>'file deleted successfully'],200);
    }
    public function bulk_check_in(Request $request)
{
    try {
        DB::beginTransaction();

        $file_ids = explode(',', $request->input('ids'));
        $max_allowed_files = 5; 

        $processed_files = 0;

        foreach ($file_ids as $file_id) {
            $file = File::findOrFail($file_id);

            if ($file->status != 'free') {
                DB::rollBack();
                return response()->json(['message' => 'You cannot take the file because it is reserved'], 403);
            }

            Reservation::create([
                'user_id' => Auth::guard('user-api')->id(),
                'file_id' => $file->id,
                'type' => 'reserved',
            ]);

            $processed_files++;

            if ($processed_files >= $max_allowed_files) {
                DB::rollBack();
                return response()->json(['message' => 'Exceeded the maximum number of files to be booked'], 403);
            }
        }

        foreach ($file_ids as $file_id) {
            $file = File::findOrFail($file_id);
            $file->status = 'taken';
            $file->save();
        }

        DB::commit();

        return response()->json(['message' => 'Files reserved successfully']);
    } catch (\Exception $ex) {
        DB::rollBack();
        return response()->json(['message' => $ex->getMessage()], 500);
    }

}









    // public function bulk_check_in(Request $request){
    // try {
    //     DB::beginTransaction();

    //     $file_ids = explode(',', $request->input('ids'));

    //     foreach ($file_ids as $file_id) {
    //         $file = File::findOrFail($file_id);

    //         if ($file->status != 'free') {
    //             DB::rollBack();
    //             return response()->json(['message' => 'You cannot take the file because it is reserved'], 403);
    //         }

    //         Reservation::create([
    //             'user_id' => Auth::guard('user-api')->id(),
    //             'file_id' => $file->id,
    //             'type' => 'reserved',
    //         ]);
    //     }

    //     foreach ($file_ids as $file_id) {
    //         $file = File::findOrFail($file_id);
    //         $file->status = 'taken';
    //         $file->save();
    //     }

    //     DB::commit();
    //     return response()->json(['message' => 'Files reserved successfully']);
    //     } catch (\Exception $ex) {
    //     DB::rollBack();
    //     return response()->json(['message' => $ex->getMessage()], 500);
    // }

    // }

    public function update(FileRequest $request, $id){

        $user = Auth::guard('user-api')->id();
        DB::beginTransaction();
        $file = File::LockForUpdate()->findOrFail($id);
        // if (!$file){

        //     return response()->json(['message'=>"we  "]);
        //     }
        $reserved = Reservation::byUser($user)->byFile($id)->notActive()->first();
        if (!$reserved){
        // abort(401);
         return response()->json(['message'=>"you can't update this file "],401);
        }
        $path = $request->file('file')->store('uploads');
        $file->update([
            'name' => $request->file('file')->getClientOriginalName(),
            'status' => 'free',
            'path'=>$path,

        ]);
        Reservation::create([
            'user_id'=>$user ,
            'file_id'=>$id ,
            'status'=> 1 ,
            'type'=> 'release'
        ]);

        $reserved->status = 1;
        $reserved->save();

        Reports::create([
            'file_id' => $file->id,
            'event_type' => 'update',
            'event_date' => now(),
            'user_id' => $user,
        ]);

        DB::commit();
        return response()->json(['message'=>'updated file successfully']);
    }
    public function getallfileuser($user_id ){
        $files=file::byuser($user_id)->get();
        return response()->json(['mesaage'=> "These All files The user has uploded",$files]);

    }

    public function getReports()
    {
        try {
            $reports = Reports::with(['user', 'file'])->get();

            if ($reports->isEmpty()) {
                return response()->json(['error' => 'Reports not found'], 404);
            }

            $data = $reports->map(function ($report) {
                return [
                    'id' => $report->id,
                    'file_name' => $report->file->name,
                    'event_type' => $report->event_type,
                    'event_date' => $report->event_date,
                    'user_name' => $report->user->name,
                ];
            });

            return response()->json(['data' => $data]);

        } catch (\Exception $ex) {
            return response()->json(['message' => $ex->getMessage()], 500);
        }
    }


    public function getallfilereserved(){

        try {

           $res=Reservation::with('user','file')->get();
           if ( $res->isEmpty()) {
            return response()->json(['error' => 'reserved is empty'], 404);
        }
        $data = $res->map(function ($res) {
            return [
                'id' => $res->id,
                'file_name' => $res->file->name,
                'status' => $res->file->status,
                'type' => $res->type,
                'user_name' => $res->user->name,
            ];
        });


        return response()->json(['data' => $data]);

    } catch (\Exception $ex) {
        return response()->json(['message' => $ex->getMessage()], 500);
    }
}
















    // restore file after edit
    public function restore_file(Request $request)
    {
        $user = auth('api')->user();
        $request->validate([
            'file' => 'required|mimes:pdf,doc,docx|max:2048', // Adjust the file types and size limit as needed
        ]);

        // Handle file upload
        $uploadedFile = $request->file('file');
        $fileName = $uploadedFile->getClientOriginalName();
        $filePath = $uploadedFile->storeAs('files', $fileName, 'public');
        $file = File::find($request->file_id);
        // Create a new file record in the database
        $data = [
            'name' => $fileName,
            'status' => 'free',
            'path' => $filePath, // Store the file path in the database
        ];
        $file->update($data);
        $user_file = new UserFile;
        $user_file->user_id = $user->id;
        $user_file->file_id = $file->id;
        $user_file->status = 'restored';
        $user_file->save();
        return response()->json([
            'status'=>200,
            'file'=>$file
        ],200);
    }
    public function requestFile()
    {
        // Get a free file
        $file = File::where('status', 'free')->first();

        if ($file) {
            // Change the status to in_progress
            $file->update(['status' => 'in_progress']);

            return response()->json($file);
        } else {
            return response()->json(['message' => 'No free files available'], 404);
        }
    }


    public function modifyFile($id)
    {
        // Find the file by ID
        $file = File::find($id);

        if ($file && $file->status == 'free') {
            // Change the status to in_progress
            $file->update(['status' => 'in_progress']);

            return response()->json($file);
        } else {
            return response()->json(['message' => 'File not found or not free'], 404);
        }


    }

    public function reviewFilesByStatus($status)
    {
        // Validate the provided status
        $validStatuses = ['free', 'in_progress', 'reserved'];

        if (!in_array($status, $validStatuses)) {
            return response()->json(['message' => 'Invalid status provided'], 400);
        }

        // Get files based on the provided status
        $files = File::where('status', $status)->get();

        return response()->json($files);
    }



    public function checkIn(Request $request)
    {
        $user = Auth::user();

        // Validate request data
        $request->validate([
            'file_ids' => 'required|array',
            'group_id' => 'required|exists:groups,id',
        ]);

        $fileIds = $request->input('file_ids');
        $groupId = $request->input('group_id');

        // Check if all selected files belong to the specified group
        if (File::whereIn('id', $fileIds)->where('group_id', $groupId)->where('is_free', false)->exists()) {
            return response()->json(['error' => 'One or more selected files are already in use.'], 422);
        }

        // Use a transaction to ensure atomic operations
        DB::transaction(function () use ($fileIds, $user) {
            // Reserve the selected files for the user
            foreach ($fileIds as $fileId) {
                Reservation::create([
                    'file_id' => $fileId,
                    'user_id' => $user->id,
                ]);

                File::where('id', $fileId)->update([
                    'is_free' => false,
                    'user_id' => $user->id,
                ]);
            }
        });

        return response()->json(['message' => 'Files reserved successfully.']);
    }

    public function checkOut(Request $request)
    {
        $user = Auth::user();

        // Validate request data
        $request->validate([
            'file_id' => 'required|exists:files,id,is_free,0,user_id,' . $user->id,
        ]);

        $fileId = $request->input('file_id');

        // Use a transaction to ensure atomic operations
        DB::transaction(function () use ($fileId, $user) {
            // Update the file status to free
            File::where('id', $fileId)->update([
                'is_free' => true,
                'user_id' => null,
            ]);

            // Delete the reservation record
            Reservation::where('file_id', $fileId)->delete();
        });

        return response()->json(['message' => 'File checked out successfully.']);
    }
}
