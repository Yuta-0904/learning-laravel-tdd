<?php

namespace Tests\Factories\Traits;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Support\Arr;

trait CreatesUser
{
    private function createUser(array $options = []): User
    {
        $userFactory = User::factory();
        $userStates = Arr::get($options, 'states.user', []);
        if(!empty($userStates)) {
            $randState = $userStates[array_rand($userStates, 1)];
            $user = $userFactory->$randState()->create();
            //attributesで任意のユーザが作りたいケースがあるのであれば
            //$user = $userFactory->create(Arr::get($options, 'attributes.user', []));
        } else {
            $user = $userFactory->create();
        }
        
        $userProfileFactory = UserProfile::factory();
        $userProfileStates = Arr::get($options, 'states.user_profile', []);
        if(!empty($userProfileStates)) {
            $randState = $userProfileStates[array_rand($userProfileStates, 1)];
            $profile = $userProfileFactory->$randState()->create(['user_id' => $user->id]);
            //attributesで任意のユーザプロフィールが作りたいケースがあるのであれば
            // $user->profile()->save(
            //         $userProfileFactory->make(Arr::get($options, 'attributes.user_profile', []))
            // );
        } else {
            $profile = $userProfileFactory->create(['user_id' => $user->id]);
        }
               
        return $user;
    }
}
