<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;




    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [  // معناه مين من الكولم مسموح انه نضيف محتوى فيهم
        'name',
        'email',
        'age',
    ];

        /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<string>
     */
    protected $hidden =
    [
        'created_at',
        'updated_at',
    ];

        /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'age' => 'boolean',
    ];

}
