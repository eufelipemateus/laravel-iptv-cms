<?php

namespace Tests\Feature\Schedule;

use App\Console\Kernel;
use Illuminate\Console\Scheduling\Schedule;
use Tests\TestCase;

class StoreResetScheduleTest extends TestCase
{
    public function test_store_reset_command_is_scheduled_every_twelve_hours(): void
    {
        /** @var Kernel $kernel */
        $kernel = app(Kernel::class);

        /** @var Schedule $schedule */
        $schedule = app(Schedule::class);

        $scheduleMethod = new \ReflectionMethod($kernel, 'schedule');
        $scheduleMethod->setAccessible(true);
        $scheduleMethod->invoke($kernel, $schedule);

        $events = collect($schedule->events());
        $event = $events->first(fn ($scheduledEvent) => str_contains($scheduledEvent->command, 'store:reset-demo-data'));

        $this->assertNotNull($event);
        $this->assertSame('0 */12 * * *', $event->expression);
        $this->assertTrue($event->withoutOverlapping);
    }
}
