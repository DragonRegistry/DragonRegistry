<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class Package extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'type', 'uuid', 'creator_user_id', 'name', 'storage_path'
    ];

    public function versions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PackageVersion::class);
    }

    public function creator(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_user_id');
    }

    public function getTag(): string
    {
        $tagSubject = Str::afterLast($this->name, '/');
        return Str::slug($tagSubject, "_");
    }

    public function getPath($fileName, $versionTag = null): string
    {
        $s = DIRECTORY_SEPARATOR;
        if(!empty($versionTag))
            $versionTag .= $s;
        return storage_path("node" . $s . $this->storage_path . $s . $versionTag . $fileName);
    }

    public static function Initialize($name): Package
    {
        $uuid = Str::uuid()->toString();
        $i = new self([
            'type' => 'node',
            'uuid'=> $uuid,
            'creator_user_id' => Auth::id(),
            'name' => $name,
            'storage_path' => "$uuid"
        ]);
        $i->save();
        return $i;
    }
}
