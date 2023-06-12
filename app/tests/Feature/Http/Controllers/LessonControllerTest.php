<?php

namespace Tests\Feature\Http\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Tests\TestCase;
use App\Models\Lesson;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\Reservation;
use Tests\Factories\Traits\CreatesUser;


class LessonControllerTest extends TestCase
{
    use RefreshDatabase;
    use CreatesUser;
   
     /**
     * @param int $capacity
     * @param int $reservationCount
     * @param string $expectedVacancyLevelMark
     * @param string $button
     * @dataProvider dataShow
     */
    public function testShow(int $capacity, int $reservationCount, string $expectedVacancyLevelMark,string $button)
    {
        $lesson = Lesson::factory()->create(['capacity'=>$capacity]);
        for ($i=0; $i < $reservationCount; $i++) { 
            $user = User::factory()->create();
            UserProfile::factory()->create(['user_id' => $user->id]);
            Reservation::factory()->create(['user_id'=>$user->id,'lesson_id'=>$lesson->id]);
        }

        // 状態やプロパティを指定したい場合
        // テストケースによって変化するならデータプロバイダから渡すといいでしょう
        $options = [
            'states' => [
                'user' => ['lessThan1Year','lessThan2Year'],
                'user_profile' => ['goldMember','seniorMember'],
            ]
        ];
        $user = $this->createUser($options);



        $this->actingAs($user);

        $response = $this->get("/lessons/{$lesson->id}");

        $response->assertStatus(Response::HTTP_OK);
        $response->assertSee($lesson->name);
        $response->assertSee("空き状況: {$expectedVacancyLevelMark}");
        $response->assertSee($button, false);
    }

    public function dataShow()
    {
        $button = '<button class="btn btn-primary">このレッスンを予約する</button>';
        $span = '<span class="btn btn-primary disabled">予約できません</span>';

        return [
            '空き十分' => [
                'capacity' => 6,
                'reservationCount' => 1,
                'expectedVacancyLevelMark' => '◎',
                'button' => $button
            ],
            '空きわずか' => [
                'capacity' => 6,
                'reservationCount' => 2,
                'expectedVacancyLevelMark' => '△',
                'button' => $button
            ],
            '空きなし' => [
                'capacity' => 1,
                'reservationCount' => 1,
                'expectedVacancyLevelMark' => '×',
                'button' => $span
            ],
        ];
    }
}
