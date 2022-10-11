<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Mimusica extends Model
{
      protected $table = 'mimusicas';    
    
       protected $fillable = ['name','author','category_id'];//para solucionar error  Add [title] to fillable property to allow mass assignment on [App\Post].
       
     //Relacion de muchos a uno
     public function user() {
         return $this->belongsTo('App\User', 'user_id');
     }
     
     //Relacion de muchos a uno
     public function category() {
         return $this->belongsTo('App\Category', 'category_id');
     }
}
