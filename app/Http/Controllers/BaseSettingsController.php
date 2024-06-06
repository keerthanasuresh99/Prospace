<?php

namespace App\Http\Controllers;

use App\Models\BaseSettings;
use App\Models\SubAchieverList;
use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class BaseSettingsController extends Controller
{
    public function getAchieversList()
    {
        $list = BaseSettings::where('type', 'achiever')->where('is_deleted', 0)->get();

        return view('admin.basesettings.listAchiever', [
            'list' => $list,
        ]);
    }

    public function getSubAchieversList()
    {
        $list = SubAchieverList::where('is_deleted', 0)->latest()->with('achievers')->get();

        $achievers_list = BaseSettings::where('type', 'achiever')->where('is_deleted', 0)->get();

        return view('admin.basesettings.listSubAchiever', [
            'list' => $list,
            'achievers_lists' => $achievers_list
        ]);
    }

    public function listTemplates()
    {
        $list = Template::where('is_deleted', 0)->latest()->with('achievers', 'subAchievers')->get();
        $achievers_list = BaseSettings::where('type', 'achiever')->where('base_settings.is_deleted', 0)->get();

        return view('admin.basesettings.listTemplates', [
            'list' => $list,
            'achievers_lists' => $achievers_list
        ]);
    }

    public function store(Request $request)
    {

        $rules = array(
            'type' => 'required',
            'value' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()->with('msg_error', $validator->errors());
        }

        $exists = BaseSettings::where('type', $request->type)->where('value', $request->value)
            ->whereNotIn('type', ['achiever'])->where('is_deleted', 0)->count();

        if ($exists) {
            return redirect()->back()->with('msg_error', 'The ' . $request->type . ' name has already been taken.');
        }
        $add = new BaseSettings();
        $add->type = $request->type;
        $add->value = $request->value;
        $add->description = $request->description;
        $add->sort_order = $request->sort_order ? $request->sort_order : 0;

        if ($add->save()) {
            return redirect()->back()->with('msg_success', 'Successfully Added..');
        }

        return redirect()->back()->with('msg_error', 'Something went wrong..!!');
    }


    public function update(Request $request)
    {
        $exists = BaseSettings::where('id', $request->id)->where('is_deleted', 0)->first();
        if ($exists) {
            BaseSettings::where('id', $request->id)
                ->update([
                    'value' => $request->value,
                    'description' => $request->description,
                    'sort_order' => $request->sort_order ? $request->sort_order : $exists->sort_order,
                ]);

            return redirect()->back()->with('msg_success', 'Successfully Updated..');
        }
        return redirect()->back()->with('msg_error', 'Something went wrong..!!');
    }

    public function destroy(Request $request)
    {
        $exists = BaseSettings::where('id', $request->id)->first();
        if ($exists) {
            BaseSettings::where('id', $request->id)->update(['is_deleted' => 1]);
            SubAchieverList::where('achiever_id', $request->id)->update(['is_deleted' => 1]);
            Template::where('achiever_id', $request->id)->update(['is_deleted' => 1]);

            return redirect()->back()->with('msg_success', 'Successfully Deleted..');
        }
        return redirect()->back()->with('msg_error', 'Something went wrong..!!');
    }


    public function addSubachiever(Request $request)
    {

        $rules = array(
            'achiever' => 'required',
            'value' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()->with('msg_error', $validator->errors());
        }

        $exists = SubAchieverList::where('value', $request->value)->where('achiever_id', $request->achiever)->where('is_deleted', 0)->count();

        if ($exists) {
            return redirect()->back()->with('msg_error', 'The ' . $request->value . ' name has already been taken.');
        }

        $exists = BaseSettings::where('id', $request->achiever)->first();
        if ($exists) {
            BaseSettings::where('id', $request->achiever)
                ->update(['status' => 1]);
        }

        $add = new SubAchieverList();
        $add->achiever_id  = $request->achiever;
        $add->value = $request->value;
        if ($add->save()) {
            return redirect()->back()->with('msg_success', 'Successfully Added..');
        }

        return redirect()->back()->with('msg_error', 'Something went wrong..!!');
    }

    public function deleteSubachiever(Request $request)
    {
        $exists = SubAchieverList::where('id', $request->id)->first();
        if ($exists) {
            SubAchieverList::where('id', $request->id)->update(['is_deleted' => 1]);
            Template::where('sub_achiever_id', $request->id)->update(['is_deleted' => 1]);
            return redirect()->back()->with('msg_success', 'Successfully Deleted..');
        }
        return redirect()->back()->with('msg_error', 'Something went wrong..!!');
    }

    public function updateSubachiever(Request $request)
    {
        $exists = SubAchieverList::where('id', $request->id)->where('is_deleted', 0)->first();
        if ($exists) {
            SubAchieverList::where('id', $request->id)
                ->update([
                    'value' => $request->value,
                    'achiever_id' => $request->achiever,
                ]);

            return redirect()->back()->with('msg_success', 'Successfully Updated..');
        }
        return redirect()->back()->with('msg_error', 'Something went wrong..!!');
    }

    public function fetchSubachievers(Request $request)
    {
        $data['subachievers'] = SubAchieverList::where('achiever_id', $request->id)->where('is_deleted', 0)->get(["value", "id"]);
        return response()->json($data);
    }


    public function addTemplate(Request $request)
    {
        $rules = array(
            'achiever' => 'required',
            // 'image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        );
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()->with('msg_error', $validator->errors());
        }

        $exists = Template::where(function ($query) use ($request) {
            $query->where('achiever_id', $request->achiever);
            if ($request->sub_achiever === null) {
                $query->whereNull('sub_achiever_id');
            } else {
                $query->where('sub_achiever_id', $request->sub_achiever);
            }
        })->first();
        if ($exists) {
            return redirect()->back()->with('msg_error', 'The  template has already been added.');
        } else {

            $add = new Template();
            $add->achiever_id  = $request->achiever;
            $add->sub_achiever_id  = $request->sub_achiever;
            $add->image_position = $request->image_position;
            $add->colour_for_name = $request->color_for_name;
            $add->colour_for_date = $request->color_for_date;

            if ($request->image) {
                $extension = $request->file('image')->extension();
                $background_image = time() . mt_rand(100, 999) . '.' . $extension;
                Storage::disk('templates')->putFileAs('', $request->file('image'), $background_image);
                $add->image = $background_image;
            }

            if ($add->save()) {
                $sub_achiever_exist = SubAchieverList::where('id', $request->sub_achiever)->first();
                if ($sub_achiever_exist) {
                    SubAchieverList::where('id', $request->sub_achiever)
                        ->update(['status' => 1]);
                }

                return redirect()->back()->with('msg_success', 'Successfully Added..');
            }
        }
        return redirect()->back()->with('msg_error', 'Something went wrong..!!');
    }

    public function editTemplate(Request $request)
    {
        $template = Template::where('id', request('id'))->where('is_deleted', 0)->first();
        return response()->json($template);
    }

    public function updateTemplate(Request $request)
    {
        $exists = Template::where('id', $request->id)->where('is_deleted', 0)->first();
        if ($exists) {
            $background_image = $exists->image;
            if ($request->image) {
                $extension = $request->file('image')->extension();
                $background_image = time() . mt_rand(100, 999) . '.' . $extension;
                Storage::disk('templates')->putFileAs('', $request->file('image'), $background_image);
            }

            Template::where('id', $request->id)
                ->update([
                    'achiever_id' => $request->achiever,
                    'sub_achiever_id' => $request->sub_achiever,
                    'image' =>  $background_image,
                    'image_position' => $request->image_position,
                    'colour_for_name' => $request->color_for_name,
                    'colour_for_date' => $request->color_for_date
                ]);

            return redirect()->back()->with('msg_success', 'Successfully Updated..');
        }
        return redirect()->back()->with('msg_error', 'Something went wrong..!!');
    }


    public function deleteTemplate(Request $request)
    {
        $exists = Template::where('id', $request->id)->first();
        if ($exists) {
            Template::where('id', $request->id)->update(['is_deleted' => 1]);

            return redirect()->back()->with('msg_success', 'Successfully Deleted..');
        }
        return redirect()->back()->with('msg_error', 'Something went wrong..!!');
    }
}
