<?php

namespace App\Http\Controllers\Node;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class SystemController extends Controller
{
    public function system() {
        //@Note: I'm still debating of keeping this endpoint. It is made with couchdb data in mind, which we don't use, for now let's make it something usefull and informative I guess
        $path = storage_path('node' . DIRECTORY_SEPARATOR);
        if(!File::exists($path))
            File::makeDirectory($path);
        $totalSize = 0; //@TODO: Highly inefficient to do this every request, need to cache this data and update is periodically
        foreach (File::allFiles($path) as $file)
            $totalSize += $file->getSize();
        return [
            'db_name' => Str::snake(config('app.name')),
            'doc_count' => 0, //@TODO: Add after finishing documents, total amount of unique packages-versions
            'doc_del_count' => 0, //@TODO: Add after finishing documents, total amount of unique deleted package-versions
            'disk_size' => disk_total_space($path),
            'data_size' => $totalSize,
            'instance_start_time' => 0, //@TODO: Need to set this value in a file and fetch it when starting the instance
//            'update_seq' => 0, //This is from couchdb, nothing we can put here
//            'purge_seq' => 0, //This is from couchdb, nothing we can put here
//            'compact_running' => false, //This is from couchdb, nothing we can put here
//            'committed_update_seq' => false, //This is from couchdb, nothing we can put here
//            'disk_format_version' => false, //This is from couchdb, nothing we can put here
        ];
    }

    public function getPackageInfo($package) {

    }
}
