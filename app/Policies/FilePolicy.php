<?php

namespace App\Policies;

use App\Models\User;
use App\Models\File;
use Illuminate\Auth\Access\HandlesAuthorization;

class FilePolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        
    }
    
    public function download(User $user, File $file)
    {
        if($file->user_id === $user->id){
            return true;
        }
        
        // TODO: Проверить права доступа к файлу
        return true;
        
        return false;
    }
    
}
