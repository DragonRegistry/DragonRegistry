<?php

namespace App\Http\Controllers\Node;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\PackageVersion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PackageController extends NodeController
{
    private function verifyPackage($package, string $versionTag = null)
    {
        $package = Package::query()->where('type', 'node')->where('name', $package)->first();
        if (empty($package))
            return false;
        if(!empty($versionTag)) {
            if ($versionTag == 'latest')
                $version = PackageVersion::query()
                    ->where('package_id', $package->id)->orderByDesc('id')->first();
            else
                $version = PackageVersion::query()
                    ->where('package_id', $package->id)->where('version', $versionTag)->first();
            if (empty($version))
                return false;
        }
        return [$package, $version ?? null];
    }

    public function getPackageInfo($package)
    {
        if(strpos($package, "%2f") !== false) {
            $exploded = explode("%2f", $package);
            $package = "@$exploded[0]/$exploded[1]";
        }
        $verify = $this->verifyPackage($package);
        if($verify === false)
            return $this->error('Not found', 404);
        $package = $verify[0];
        $path = storage_path("node" . DIRECTORY_SEPARATOR . $package->storage_path . DIRECTORY_SEPARATOR . "package.json");
        if (!File::exists($path))
            return $this->error('Package malformed', 500);
        return response()->json(json_decode(File::get($path)));
    }

    public function getScopedPackageInfo($scope, $package)
    {
        return $this->getPackageInfo("@$scope/$package");
    }

    public function getPackageVersionInfo($package, $version)
    {
        if(strpos($package, "%2f") !== false) {
            $exploded = explode("%2f", $package);
            $package = "@$exploded[0]/$exploded[1]";
        }
        $verify = $this->verifyPackage($package, $version);
        if($verify === false || empty($verify[1]))
            return $this->error('Not found', 404);
        $package = $verify[0];
        $version = $verify[1];
        $path = storage_path("node" . DIRECTORY_SEPARATOR . $package->storage_path . DIRECTORY_SEPARATOR . $version->version . DIRECTORY_SEPARATOR . "package.json");
        if (!File::exists($path))
            return $this->error('Package malformed', 500);
        return response()->json(json_decode(File::get($path)));
    }

    public function getScopedPackageVersionInfo($scope, $package, $version)
    {
        return $this->getPackageVersionInfo("@$scope/$package", $version);
    }

    public function downloadPackage($package, $tarname)
    {
        if(strpos($package, "%2f") !== false) {
            $exploded = explode("%2f", $package);
            $package = "@$exploded[0]/$exploded[1]";
        }
        if(!Str::endsWith($tarname, ".tar.gz"))
            return $this->error('Not found', 404);
        $fileName = Str::remove(".tar.gz", $tarname);
        $version = Str::afterLast($fileName, "-");
        $verify = $this->verifyPackage($package, $version);
        if($verify === false || empty($verify[1]))
            return $this->error('Not found', 404);
        $package = $verify[0];
        $version = $verify[1];
        $path = storage_path("node" . DIRECTORY_SEPARATOR . $package->storage_path . DIRECTORY_SEPARATOR . $version->version . DIRECTORY_SEPARATOR . "package.tar.gz");
        if (!File::exists($path))
            return $this->error('Package malformed', 500);
        return response()->download($path, $package->getTag() . "-" . $version->version . ".tar.gz");
    }

    public function downloadScopedPackage($scope, $package, $tarname)
    {
        return $this->downloadPackage("@$scope/$package", $tarname);
    }
}
