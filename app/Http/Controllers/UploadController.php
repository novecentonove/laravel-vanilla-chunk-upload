<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    public function upload(Request $request)
    {
        // Recupera i dati del file inviati dal client
        $file = $request->file('file');
        $chunk_index = $request->input('chunk_index');
        $total_chunks = $request->input('chunks_total');

        // Crea una directory temporanea per salvare i chunk del file
        $temp_folder = 'temp/'. $request->input('uuid');
        Storage::makeDirectory($temp_folder);

        // Scrive i dati del file nel server
        $file->storeAs($temp_folder, $chunk_index, 'local');

        // Se questo è l'ultimo chunk del file, salva il file in una directory permanente
        if ($chunk_index == $total_chunks - 1) {
            $final_file = '';
            for ($i = 0; $i < $total_chunks; $i++) {
                $final_file .= Storage::get($temp_folder.'/'.$i);
            }

            // Salva il file in una directory permanente
            Storage::put('files/'.$file->getClientOriginalName(), $final_file);

            // Elimina la directory temporanea
            Storage::deleteDirectory($temp_folder);
        }

        // Restituisce una risposta al client per confermare che il file è stato caricato correttamente
        return response()->json([
            'status' => 'success',
            'chunk_index' => $chunk_index,
            'total_chunks' => $total_chunks,
            'uuid' => $request->input('uuid')
        ]);
    }
}