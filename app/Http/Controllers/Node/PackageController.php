<?php

namespace App\Http\Controllers\Node;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\PackageVersion;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;

class PackageController extends NodeController
{
    private function verify($package, string $versionTag = null)
    {
        if (strpos($package, "%2f") !== false) {
            $exploded = explode("%2f", $package);
            $package = (Str::startsWith($exploded[0], "@") ? "" : "@") . "$exploded[0]/$exploded[1]";
        }
        $package = Package::query()->where('type', 'node')->where('name', $package)->first();
        if (empty($package))
            return false;
        if (!empty($versionTag)) {
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

    public function getInfo($package)
    {
        /**
         * Example of how to check auth:
         * $pat = PersonalAccessToken::findToken(\request()->bearerToken());
         * if (!$pat)
         *     return $this->error('Unauthorized', 401);
         * $user = $pat->tokenable();
         * if (!$user)
         *     return $this->error('Unauthorized', 401);
         */
        $verify = $this->verify($package);
        if ($verify === false)
            return $this->error('Not found', 404);
        $package = $verify[0];
        $path = $package->getPath("package.json");
        if (!File::exists($path))
            return $this->error('Package malformed', 500);
        return response()->json(json_decode(File::get($path)));
    }

    public function getScopedInfo($scope, $package)
    {
        return $this->getInfo("@$scope/$package");
    }

    public function getVersionInfo($package, $version)
    {
        $verify = $this->verify($package, $version);
        if ($verify === false || empty($verify[1]))
            return $this->error('Not found', 404);
        $package = $verify[0];
        $version = $verify[1];
        $path = $package->getPath("package.json", $version->version);
        if (!File::exists($path))
            return $this->error('Package malformed', 500);
        return response()->json(json_decode(File::get($path)));
    }

    public function getScopedVersionInfo($scope, $package, $version)
    {
        return $this->getVersionInfo("@$scope/$package", $version);
    }

    public function download($package, $version, $tarname)
    {
        $verify = $this->verify($package, $version);
        if ($verify === false || empty($verify[1]))
            return $this->error('Not found', 404);
        $package = $verify[0];
        $version = $verify[1];
        $path = $package->getPath("package.tgz", $version->version);
        if (!File::exists($path))
            return $this->error('Package malformed', 500);
        return response()->download($path, $package->getTag() . "-" . $version->version . ".tar.gz");
    }

    public function downloadScoped($scope, $package, $version, $tarname)
    {
        return $this->download("@$scope/$package", $version, $tarname);
    }

    public function put(Request $request, $package)
    {
        $pat = PersonalAccessToken::findToken(\request()->bearerToken());
        if (!$pat)
            return $this->error('Unauthorized', 401);
        $user = $pat->tokenable;
        if (!$user)
            return $this->error('Unauthorized', 401);
        Auth::login($user);
        $verify = $this->verify($package);
        if ($verify != false && !empty($verify[0])) {
            if ($verify[0]->creator_user_id != Auth::id())
                return $this->error('Forbidden', 403);

            $packageVersions = $verify[0]->versions;
            $submittedVersions = $request->all()['versions'];
            if ($packageVersions->count() < sizeof($submittedVersions))
                return $this->publish($request, $package, $verify);
            if ($packageVersions->count() > sizeof($submittedVersions))
                return $this->unpublish($request, $package, $verify);
            return $this->patch($request, $package, $verify);
        }
        return $this->publish($request, $package, $verify);
    }

    private function patch(Request $request, $packageTag, $verify)
    {
        $package = $verify[0];
        File::put($package->getPath("package.json"), json_encode($request->all(), JSON_UNESCAPED_SLASHES));
    }

    public function delete(Request $request, $package)
    {
        $pat = PersonalAccessToken::findToken(\request()->bearerToken());
        if (!$pat)
            return $this->error('Unauthorized', 401);
        $user = $pat->tokenable;
        if (!$user)
            return $this->error('Unauthorized', 401);
        Auth::login($user);
        $verify = $this->verify($package);
        if($verify != false && !empty($verify[0])) {
            if ($verify[0]->creator_user_id != Auth::id())
                return $this->error('Forbidden', 403);
            $verify[0]->delete();
            return [
                'success' => true,
                'ok' => 'deleted'
            ];
        }
        return $this->error('Unknown package', 400);
    }

    public function deleteScoped(Request $request, $scope, $package)
    {
        return $this->delete($request, "@$scope/$package");
    }

    private function unpublish(Request $request, $packageTag, $verify)
    {
        $package = $verify[0];
        $packageVersions = $package->versions->keyBy('version');
        $submittedVersions = $request->all()['versions'];
        $versionToUnpublish = array_diff($packageVersions->pluck('version')->all(), array_keys($submittedVersions));
        $unpublished = [];
        foreach ($versionToUnpublish as $versionTag) {
            $version = $packageVersions->get($versionTag);
            if (empty($version))
                continue;
            $version->delete();
            $unpublished[] = $versionTag;
        }

        $base = json_decode(File::get($package->getPath("package.json")), true);
        foreach ($unpublished as $i)
            unset($base['versions'][$i]);
        File::put($package->getPath("package.json"), json_encode($base, JSON_UNESCAPED_SLASHES));

        return [
            'success' => true,
            'ok' => 'unpublished ' . implode(", ", $unpublished)
        ];
    }

    private function publish(Request $request, $packageTag, $verify)
    {
        if ($verify === false || empty($verify[0])) {
            if (strpos($packageTag, "%2f") !== false) {
                $exploded = explode("%2f", $packageTag);
                $packageTag = "@$exploded[0]/$exploded[1]";
            }
            $package = Package::Initialize($packageTag);
        } else {
            $package = $verify[0];
        }
        $body = $request->all();
        $versions = array_keys($body['versions']);
        $versionTag = end($versions);
        $version = PackageVersion::query()
            ->where('package_id', $package->id)->where('version', $versionTag)->first();
        if (!empty($version)) // https://npm.community/t/is-it-possible-to-publish-the-same-version/3805.html
            return $this->error('Version tag was already used');
        PackageVersion::Initialize($package->id, $versionTag);
        File::makeDirectory($package->getPath("", $versionTag), 644, true);
        File::put($package->getPath("package.tgz", $versionTag), base64_decode(reset($body['_attachments'])['data']));
        unset($body['_attachments']);
        $body['versions'][$versionTag]['dist']['tarball'] = url("/node/$package->name/$versionTag/package.tgz");
        $base = [];
        if (File::exists($package->getPath("package.json")))
            $base = json_decode(File::get($package->getPath("package.json")), true);
        $body['dist-tags'] = array_merge($base['dist-tags'] ?? [], $body['dist-tags']);
        $body['versions'] = array_merge($base['versions'] ?? [], $body['versions']);
        File::put($package->getPath("package.json"), json_encode($body, JSON_UNESCAPED_SLASHES));
        File::put($package->getPath("package.json", $versionTag), json_encode($body['versions'][$versionTag], JSON_UNESCAPED_SLASHES));
        return [
            'success' => true,
            'ok' => 'published'
        ];
    }

    public function putScoped(Request $request, $scope, $package)
    {
        return $this->put($request, "@$scope/$package");
    }
}
