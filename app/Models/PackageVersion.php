<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PackageVersion extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['package_id', 'publisher_user_id', 'version'];

    public function package(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function publisher(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'publisher_user_id');
    }

    public static function Initialize($pid, $tag): PackageVersion
    {
        $i = new self([
            'package_id' => $pid,
            'publisher_user_id' => Auth::id(),
            'version' => $tag
        ]);
        $i->save();
        return $i;
    }
}
