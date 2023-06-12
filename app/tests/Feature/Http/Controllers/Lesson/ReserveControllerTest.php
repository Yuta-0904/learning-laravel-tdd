<?php

namespace Tests\Feature\Http\Controllers\Lesson;

use App\Notifications\ReservationCompleted;
use Illuminate\Support\Facades\Notification;
use App\Models\Lesson;
use App\Models\Reservation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Tests\Factories\Traits\CreatesUser;
use Tests\TestCase;


class ReserveControllerTest extends TestCase
{
    use RefreshDatabase;
    use CreatesUser;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testInvoke_正常系()
    {
        Notification::fake();
        $lesson = Lesson::factory()->create();
        $user = $this->createUser();
        $this->actingAs($user);

        $response = $this->post("/lessons/{$lesson->id}/reserve");

        $response->assertStatus(Response::HTTP_FOUND);
        $response->assertRedirect("/lessons/{$lesson->id}");
        // TODO データベースのアサーション
        $this->assertDatabaseHas('reservations', [
            'lesson_id' => $lesson->id,
            'user_id' => $user->id,
        ]);

        Notification::assertSentTo(
            $user,
            ReservationCompleted::class,
            function (ReservationCompleted $notification) use ($lesson) {
                return $notification->lesson->id === $lesson->id;
            }
        );
    }


    public function testInvoke_異常系()
    {
        Notification::fake();
        $lesson = Lesson::factory()->create(['capacity' => 1]);
        $anotherUser = $this->createUser();
        Reservation::factory()->create(['user_id'=>$anotherUser->id,'lesson_id'=>$lesson->id]);
        $user = $this->createUser();
        $this->actingAs($user);
        
        $response = $this->from("/lessons/{$lesson->id}")
            ->post("/lessons/{$lesson->id}/reserve");

        $response->assertStatus(Response::HTTP_FOUND);
        $response->assertRedirect("/lessons/{$lesson->id}");
        $response->assertSessionHasErrors();
        // メッセージの中身まで確認したい場合は以下の2行も追加
        $error = session('errors')->first();
        $this->assertStringContainsString('予約できません。', $error);

        $this->assertDatabaseMissing('reservations', [
            'lesson_id' => $lesson->id,
            'user_id' => $user->id,
        ]);

        Notification::assertNotSentTo($user, ReservationCompleted::class);
    }
}
