<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{

	protected $fillable = ['title', 'description', 'keywords', 'file_extensions', 'max_file_size', 'brand', 'website_tos', 'account_activation'];

}