<?php

namespace Drupal\campaignion_csv\Files;

use Drupal\campaignion_csv\Tests\ExporterFactoryStub;

/**
 * Test the timeframe file info class.
 */
class TimeframeFileInfoTest extends \DrupalUnitTestCase {

  /**
   * Create test file and prepare some test timeframes.
   */
  public function setUp() {
    parent::setUp();
    $this->path = tempnam(sys_get_temp_dir(), __FUNCTION__);
    $today = new \DateTimeImmutable((new \DateTime())->format('Y-m-d'));
    $this->todayIncludingNow = new Timeframe($today, new \DateInterval('PT25H'));
    $this->yesterday = new Timeframe($today->sub(new \DateInterval('P1D')), new \DateInterval('PT24H'));
  }

  /**
   * Remove test file.
   */
  public function tearDown() {
    unlink($this->path);
    parent::tearDown();
  }

  /**
   * Try updating the file.
   */
  protected function callUpdate($timeframe) {
    $file_info = new TimeframeFileInfo($this->path, $timeframe, new \DateInterval('PT1H'));
    $file_info->setExporterFactory(ExporterFactoryStub::withRows([[1]]));
    $file_info->update();
  }

  /**
   * Test that a non-existing file is created.
   */
  public function testUpdateCreatesNewFile() {
    // tempnam() creates the file for security reasons. We want it to not exist.
    unlink($this->path);
    $this->callUpdate($this->todayIncludingNow);
    $this->assertTrue(file_exists($this->path));
  }

  /**
   * A file is not updated when it has been modified after its timeframe.
   */
  public function testUpdateDoesntUpdateFileNewerThanEndOfInterval() {
    $this->callUpdate($this->yesterday);
    $this->assertTrue(file_exists($this->path));
    $this->assertEqual('', file_get_contents($this->path));
  }

  /**
   * A file is not updated if it was modified within its refresh interval.
   */
  public function testUpdateDoesntUpdateFileWithinRefreshInterval() {
    $this->callUpdate($this->todayIncludingNow);
    $this->assertTrue(file_exists($this->path));
    $this->assertEqual('', file_get_contents($this->path));
  }

  /**
   * Update the file after the refresh interval has passed.
   */
  public function testUpdateFileAfterRefreshInterval() {
    touch($this->path, time() - 7200);
    $this->callUpdate($this->todayIncludingNow);
    $this->assertTrue(file_exists($this->path));
    $this->assertEqual("1\n", file_get_contents($this->path));
  }

}
