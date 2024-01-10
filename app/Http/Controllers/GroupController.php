<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Group;
use App\Models\file;
use App\Models\User;
use App\Models\Reservation;
use App\Models\GroupFile;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\GroupRequest;
use App\Models\UserGroup;
use Illuminate\Support\Facades\DB;

class GroupController extends Controller
{
    public function createGroup(GroupRequest $request)
    {
        $user =Auth::guard('user-api')->id();

        DB::beginTransaction();
        $group = Group::create([
            'name' => $request->input('name'),
            'user_id' => $user,
        ]);

        $group_User = UserGroup::create([
            'user_id' => $user,
            'group_id' => $group->id
        ]);
        DB::commit();

        return response()->json(['message' => 'Group created successfully.', 'group' => $group],200);
    }

    public function getUserGroups()
    {
        $user = Auth::user();


        $userGroups = $user->groups;

        return response()->json(['user_groups' => $userGroups],200);
    }


     public function addfiletogroup($group_id, $file_id){

        $user =Auth::guard('user-api')->id();
        $file = File::FindOrFail($file_id);
        $group = Group::findOrFail($group_id);
        $is_member = UserGroup::byUser($user)->ByGroup($group_id)->first();
        // if($file->user_id != $user | !$is_member){
        //   return response()->json(['message' => "don't have permission"], 403);
        // }
        if ($file->user_id != $user || !$is_member) {
            return response()->json(['message' => "don't have permission"], 403);
        }
         $groupFile = GroupFile::FirstOrCreate([
            'group_id' => $group_id,
            'file_id' => $file_id,
          ]);
        return response()->json(['message' => 'The file has been added to the group successfully']);




    }


    public function removefilefromgroup($group_id, $file_id){

        $user =Auth::guard('user-api')->id();
        $file = File::FindOrFail($file_id);
        $group = Group::findOrFail($group_id);
        if ($file->user_id != $user)
        return response()->json(['message' => "don't have permission"], 403);
        $groupFile = GroupFile::byGroup($group_id)->byFile($file_id)->first();
        $groupFile->delete();
        return response()->json(['message' => 'The file has been removed to the group successfully'],200);


    }



    public function joingroup($group_id){

        $user = Auth::guard('user-api')->id();
        $group = Group::findOrFail($group_id);
        $is_join = UserGroup::byUser($user)->byGroup($group_id)->first();
        if($is_join)
         return response()->json(['message'=> "The user is exist in group" ],403);
        $user_group = UserGroup::create([
            'user_id' => $user,
            'group_id' => $group_id
        ]);
        return response()->json(['message'=> "The user added sucssefuly to the group"],200);

    }


    public function removeMembergroup($group_id, $member_id){

        $user = Auth::guard('user-api')->id();
        $member = User::findOrFail($member_id);
        $group = Group::findOrFail($group_id);
        $in_group = UserGroup::byUser($member_id)->ByGroup($group_id)->first();
        if ($group->user_id != $user || !$in_group)
           return response()->json(['message'=>"Dont have The  permission"],403);
           $reserverd = Reservation::ByUser($user)->notActive()->get();
           foreach ($reserverd as $value) {
            $group_file = GroupFile::ByGroup($group_id)->byFile($value->file_id)->first();
            if ($group_file)
            return response()->json(['message'=>"The user has been taking file"],403);
        }
           $in_group->delete();
        return response()->json(['message' => 'member removed form group successfully'],200);
    }



    public function destroy($id){
        $user = Auth::guard('user-api')->id();
        try {
            DB::beginTransaction();
            $group = Group::LockForUpdate()->findOrFail($id);

            if ($group->user_id != $user)
            return response()->json(['message' => "Dont have The  permission"],401);

            $groupFiles = GroupFile::byGroup($id)->get();

            foreach ($groupFiles as $value) {
                $reserverd =  Reservation::byFile($value->file_id)->notActive()->first();
                if ($reserverd){
                return response()->json(['message' => "IT has files has reserved "],403);
                }
            }

            foreach ($groupFiles as $file){
                $file->delete();
            }
          //  $group->delete();
          // $group_d = Group::byGroup($id)->first();
           $group->delete();

            DB::commit();
            return response()->json(['message' => 'group deleted successfully'],200);
        } catch (\Exception $ex) {
            DB::rollBack();
             return response()->json(['message'=> $ex->getMessage()]);
        }


    }



    public function getallgroups(){

     $group=group::all();
     return response()->json(['message' => 'These all groups in our paltform ',$group],200);

    }


    public function getallgroupwithuser(){
    //  $group=group::WithUser()->get();
    $user_id = Auth::guard('user-api')->id();
    $user = User::withGroup()->findOrFail($user_id);
     return response()->json(['message' => "These all groups with users ",$user],200);

    }



public function getgroupdetails($id)
{
    $group = Group::withUser()->with('members')->find($id);

    if (!$group) {
        return response()->json(['error' => 'Group not found'], 404);
    }

    return response()->json(['data' => $group]);
}



}
