<?php

namespace Tests\Feature\Http\Controllers\Api\Lesson;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Lesson;
use App\Models\Reservation;
use Illuminate\Http\Response;
use Tests\Factories\Traits\CreatesUser;


class ReserveControllerTest extends TestCase
{
    use RefreshDatabase;
    use CreatesUser;
   
    public function testInvoke_正常系()
    {
        $lesson = Lesson::factory()->create();
        $user = $this->createUser();
        $this->actingAs($user, 'api');

        $response = $this->postJson("/api/lessons/{$lesson->id}/reserve");
        $response->assertStatus(Response::HTTP_CREATED);
        $response->assertJson([
            'lesson_id' => $lesson->id,
            'user_id' => $user->id,
        ]);
        $this->assertDatabaseHas('reservations', [
            'lesson_id' => $lesson->id,
            'user_id' => $user->id,
        ]);
    }


    public function testInvoke_異常系()
    {
        $lesson = Lesson::factory()->create(['capacity' => 1]);
        $anotherUser = $this->createUser();
        Reservation::factory()->create(['user_id'=>$anotherUser->id,'lesson_id'=>$lesson->id]);
        
        $user = $this->createUser();
        $this->actingAs($user, 'api');

        $response = $this->postJson("/api/lessons/{$lesson->id}/reserve");
        $response->assertStatus(Response::HTTP_CONFLICT);
        $response->assertJsonStructure(['error']);
        // メッセージの中身まで確認したい場合は以下の2行も追加
        $error = $response->json('error');
        $this->assertStringContainsString('予約できません。', $error);
        
        $this->assertDatabaseMissing('reservations', [
            'lesson_id' => $lesson->id,
            'user_id' => $user->id,
        ]);
    }
}
