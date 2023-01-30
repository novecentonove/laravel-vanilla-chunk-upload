<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    public function upload(Request $request)
    {
        try {

            $file           = $request->file('file');
            $file_name      = $request->input('file_name');
            $chunk_index    = (int) $request->input('chunk_index');
            $total_chunks   = (int) $request->input('chunks_total');
            $percent        = (int) $request->input('percent');
            
            // Get you storage folder eg. config(app.media_folder);
            $STORAGE_DIR = 'media';
            $MAX_UPLOAD_MB = 30; // MB
            $MIME_TYPE_ACCEPTED = ['image/jpeg', 'image/png']; // mime types

            $temp_folder = 'temp/'. $request->input('uuid');

            if(!File::exists($temp_folder)) {
                Storage::makeDirectory($temp_folder);
            }

            $file->storeAs($temp_folder, $chunk_index, 'local');

            // If this is the last chunk, I save it to a permanent directory
            if ($chunk_index === $total_chunks - 1) {

                $final_file = '';

                for ($i = 0; $i < $total_chunks; $i++) {
                    $final_file .= Storage::get($temp_folder.'/'.$i);
                }

                // validation
                $finfo = new \finfo(FILEINFO_MIME_TYPE);
                $mimeType = $finfo->buffer($final_file);
                $size = $this->stringLengthToMegabytes($final_file);

                if(!in_array($mimeType, $MIME_TYPE_ACCEPTED)){
                    Storage::deleteDirectory($temp_folder);
                    return response()->json(['message' => 'File type not valid'], 413);
                }

                if($size > $MAX_UPLOAD_MB){
                    Storage::deleteDirectory($temp_folder);
                    return response()->json(['message' => 'file too large'], 413);
                }

                // Save file
                Storage::put($STORAGE_DIR . '/' . $file_name, $final_file);

                // Delete temp folder
                Storage::deleteDirectory($temp_folder);
            }

            // Restituisce una risposta al client per confermare che il file è stato caricato correttamente
            return response()->json([
                'chunk_index' => $chunk_index,
                'total_chunks' => $total_chunks,
                'percent' => $percent
            ]);

        } catch(\Exception $e){
            return response()->json([
                'message' =>$e->getMessage()
            ], 500);
        }
    }

    protected function stringLengthToMegabytes($string) {
        $length = strlen($string);
        return round($length / (1024 * 1024), 2);
    }
}
