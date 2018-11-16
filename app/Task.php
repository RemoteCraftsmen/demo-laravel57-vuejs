<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $appends = ['path'];

    public $fillable = ['name', 'completed'];

    public function path()
    {
        return "/tasks/{$this->id}";
    }

    public function getPathAttribute()
    {
        return $this->path();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
