<?php

namespace App\Http\Controllers;

use App\Http\Resources\DispListEntryCollection;
use App\Http\Resources\DispListEntryResource;
use App\Models\DispList;
use App\Models\DispListEntry;
use Illuminate\Validation\Factory as Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DispListEntriesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(DispList $displist)
    {
        $this->authorize('view', [DispListEntry::class, null, $displist]);
        return new DispListEntryCollection($displist->entries()->orderBy('order')->get());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Validator $v, DispList $displist, Request $request)
    {
        $this->authorize('create', [DispListEntry::class, $displist]);

        $validator = $v->make($request->all(), [
            'id' => 'required|uuid',
            'first_name' => 'required|string|min:1|max:128|not_regex:/[^а-яё \-]/iu',
            'middle_name' => 'nullable|min:1|max:128|not_regex:/[^а-яё \-]/iu',
            'last_name' => 'required|string|min:1|max:128|not_regex:/[^а-яё \-]/iu',
            'birthday' => 'required|date',
            'enp' => 'required|string|size:16',
            'snils' => 'required|string|size:11',
            'preventive_medical_measure_id' => [
                'required','integer','min:1'
            ],
            'description' => 'nullable|string|min:1|max:256',
            'contact_info' => 'nullable|string|min:1|max:256',
            //'mo_id' => '',
            //'smo_id' => '',
            //'insurOgrn' => '',
            //'status_id' => '',
            //'status_text' => '',
            //'user_id' => '',
            //'organization_id' => '',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }
        $validated = $validator->validated();

        $user = Auth::user();
        $userId = $user->id;

        // $preventiveMedicalMeasureId = PreventiveMedicalMeasureTypes::firstWhere('code', $validated['preventive_medical_measure_code'])->id;

        $entry = DispListEntry::create(array_merge(
            $validated,
            [
                'displist_id' => $displist->id,
                'status_id' => 1,
                'user_id' => $userId,
                // 'preventive_medical_measure_id' => $preventiveMedicalMeasureId
            ],
        ));
        return new DispListEntryResource($entry);
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
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Validator $v, DispList $displist, DispListEntry $entry)
    {
        if ($entry->displist_id !== $displist->id)
        {
            return response()->json(['error' => 'Forbidden'], 403);
        }
        $this->authorize('update', [$entry, $displist]);

        $validator = $v->make($request->all(), [
            'first_name' => 'required|string|min:1|max:128|not_regex:/[^а-яё \-]/iu',
            'middle_name' => 'nullable|min:1|max:128|not_regex:/[^а-яё \-]/iu',
            'last_name' => 'required|string|min:1|max:128|not_regex:/[^а-яё \-]/iu',
            'birthday' => 'required|date',
            'enp' => 'required|string|size:16',
            'snils' => 'required|string|size:11',
            'preventive_medical_measure_id' => [
                'required','integer','min:1'
            ],
            'description' => 'nullable|string|min:1|max:256',
            'contact_info' => 'nullable|string|min:1|max:256',
            //'mo_id' => '',
            //'smo_id' => '',
            //'insurOgrn' => '',
            //'status_id' => '',
            //'status_text' => '',
            //'user_id' => '',
            //'organization_id' => '',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }
        $validated = $validator->validated();

        $user = Auth::user();
        $userId = $user->id;
        $org  = $user->organization;
        $entry->user_id = $userId;
        $entry->status_id = 1;
        $entry->first_name =  $validated['first_name'];
        $entry->middle_name =  $validated['middle_name'];
        $entry->last_name =  $validated['last_name'];
        $entry->birthday =  $validated['birthday'];
        $entry->enp =  $validated['enp'];
        $entry->snils =  $validated['snils'];
        $entry->preventive_medical_measure_id =  $validated['preventive_medical_measure_id'];
        $entry->description =  $validated['description'] ?? null;
        $entry->contact_info =  $validated['contact_info'] ?? null;
        $entry->save();
        return new DispListEntryResource($entry);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(DispList $displist, DispListEntry $entry)
    {
        if ($entry->displist_id !== $displist->id)
        {
            return response()->json(['error' => 'Forbidden'], 403);
        }
        $this->authorize('delete', [$entry, $displist]);

        return $entry->delete();
    }
}
