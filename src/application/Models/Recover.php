<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recover extends Model
{

	protected $fillable = ['uid','key', 'used'];

}