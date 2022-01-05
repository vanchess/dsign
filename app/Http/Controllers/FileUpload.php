<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Validator;

use App\Models\File;
use App\Models\FileSignStamp;
use App\Models\FileSignStampType;

use App\Http\Resources\FileResource;
use App\Http\Resources\FileCollection;


class FileUpload extends Controller
{
    private const EXT = 'odt,ods,odp,csv,txt,xlx,xls,pdf,doc,docx,xlsx,xml,ppt,oms,zip';
    private const MAX_SIZE = 15360; // КБ
    
    public function __construct()
    {
        $this->middleware('auth:api');
    }
    
    public function fileUpload(Request $request){
        
        if ($request->hasFile('file')) {
            $userId = Auth::id();

            $validator = Validator::make([
                  'file'      => $request->file,
                  'extension' => strtolower($request->file->getClientOriginalExtension()),
              ],
              [
                  'file'      => 'required|max:' . FileUpload::MAX_SIZE,
                  'extension' => 'required|in:' . FileUpload::EXT,
              ]);

            if($validator->fails()){
                return response()->json($validator->errors()->toJson(), 400);
            }

            if($request->file()) {
                $extension = strtolower($request->file->getClientOriginalExtension()); // $request->file('file')->extension();
                
                $filename = uniqid('',true).'.'.$extension;

                $path = "UserFiles";
                
                $fileOriginalName = $request->file('file')->getClientOriginalName();
                $filePath = $request->file('file')->storeAs($path, $filename);
                
                $fileModel = new File;
                $fileModel->name = $fileOriginalName;
                $fileModel->file_path   = $filePath;
                $fileModel->user_id     = $userId;
                $fileModel->description = '';
                $fileModel->save();

                FileResource::withoutWrapping();
                return new FileResource($fileModel);
                //return response()->json(['success' => 'File has been uploaded.'], 200);
            }
        }
        return response()->json('Error. No file', 400);
    }
    
    public function fileUploadMultiple(Request $request){
        
        $userId = Auth::id();
        
        $files = $request->file('attachment');

        if ($request->hasFile('attachment')) {
            foreach ($files as $file) {
                $validator = Validator::make([
                    'file'      => $file,
                    'extension' => strtolower($file->getClientOriginalExtension()),
                ],
                [
                  'file'      => 'required|max:' . FileUpload::MAX_SIZE,
                  'extension' => 'required|in:'  . FileUpload::EXT,
                ]);
                if($validator->fails()){
                    return response()->json($validator->errors()->toJson(), 400);
                }
            }
        }
        $result = [];
        $path = "UserFiles";
        if ($request->hasFile('attachment')) {
            
            foreach ($files as $file) {
                $extension = strtolower($file->getClientOriginalExtension());//$file->extension();
                
                $filename = uniqid('',true).'.'.$extension;

                $fileOriginalName = $file->getClientOriginalName();
                $filePath = $file->storeAs($path, $filename);
                
                $fileModel = new File;
                $fileModel->name = $fileOriginalName;
                $fileModel->file_path   = $filePath;
                $fileModel->user_id     = $userId;
                $fileModel->description = '';
                $fileModel->save();

                $result[] = $fileModel;
                //return response()->json(['success' => 'File has been uploaded.'], 200);
            }
        }
        return new FileCollection($result);
    }
    
    public function fileDownload($id){
        return $this->download($id, 'original');
    }
    
    public function fileStampedDownload($id){
        return $this->download($id, 'stamped');
    }
    
    public function filePdfDownload($id){
        return $this->download($id, 'pdf');
    }
    
    public function download($id, $type){
        $user = Auth::user();
        
        $file = File::find($id);
        
        if ($file === null) {
           return response()->json(['error' => 'Forbidden'], 403);
        }
        
        $this->authorize('download', $file);


        if ($type === 'original') {
            return Storage::download($file->file_path, $file->name);
        }
        
        // Тип штампа ЭП (по умолчанию дополнительная страница)
        $type_id = FileSignStampType::where('name','additionalPage')->firstOrFail()->id;
        $fileStamp = FileSignStamp::where('file_id',$file->id)
                    ->where('type_id', $type_id)
                    ->where('user_id', null)
                    ->first();
        
        if ($fileStamp === null) {
           return response()->json(['error' => 'Forbidden'], 403);
        }
        
        if($type === 'stamped' && $fileStamp->stamped_file_path){
            return Storage::download($fileStamp->stamped_file_path, $file->name . '.pdf');
        }
        if($type === 'pdf' && $fileStamp->pdf_file_path){
            return Storage::download($fileStamp->pdf_file_path, $file->name . '.pdf');
        }
        
        return response()->json(['error' => 'Forbidden'], 403);
    }
}