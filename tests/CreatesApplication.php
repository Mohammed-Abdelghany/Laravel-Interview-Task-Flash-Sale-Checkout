<?php

namespace Tests;

use Illuminate\Foundation\Application;

trait CreatesApplication
{
  /**
   * Creates the application.
   *
   * @return \Illuminate\Foundation\Application
   */
  public function createApplication(): Application
  {
    $app = require __DIR__ . '/../bootstrap/app.php';

    // Bootstrap the application like the HTTP kernel would.
    $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

    return $app;
  }
}
