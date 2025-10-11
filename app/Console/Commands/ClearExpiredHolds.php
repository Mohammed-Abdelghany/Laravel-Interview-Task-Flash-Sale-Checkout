<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\HoldService;

class ClearExpiredHolds extends Command
{
  /**
   
   *
   * @var string
   */
  protected $signature = 'holds:clear';

  /**

   *
   * @var string
   */
  protected $description = 'Automatically remove expired holds and free reserved stock';

  public function handle(HoldService $holdService): int
  {
    $count = $holdService->releaseExpiredHolds();

    if ($count > 0) {
      $this->info("{$count} expired hold(s) cleared successfully.");
    } else {
      $this->line('No expired holds found.');
    }

    return self::SUCCESS;
  }
}
