<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Pion\Laravel\ChunkUpload\Exceptions\UploadMissingFileException;
use Pion\Laravel\ChunkUpload\Receiver\FileReceiver;
use Pion\Laravel\ChunkUpload\Handler\ContentRangeUploadHandler;

class FileStoreController extends Controller
{
    public function __invoke(Request $request){
        // throwing an exception to test if the application can detect the error
        // throw new Exception();

        $reciever = new FileReceiver(
            UploadedFile::fake()->createWithContent('file', $request->getContent),
            $request,
            ContentRangeUploadHandler::class
        );

        // tryin to catch the error if upload fails
        if($reciever->isUploaded() === false){
            throw new UploadMissingFileException();
        }

        $save = $reciever->receive();

        if($save->isFinished()){
            $this->storeFile($request, $save->getFile());
        }

        $save->handler();
    }

    protected function storeFile(Request $request, UploadedFile $file){
        $request->user()->files()->create([
            // store ina directory called videos in the public system
            'file_path' => $file->storeAs('videos', Str::uuid(), 'public')
        ]);
    }
}
