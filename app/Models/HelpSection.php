<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HelpSection extends Model
{
    use HasFactory;
    protected $table = 'help_sections';
    protected $connection = "mysql";
    protected $fillable = ['role', 'url', 'type', 'title'];
}
