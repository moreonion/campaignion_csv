<?php

namespace Drupal\campaignion_csv\Files;

use Drupal\campaignion_csv\Tests\ExporterFactoryStub;

/**
 * Test the timeframe file info class.
 */
class SingleFileInfoTest extends \DrupalUnitTestCase {

  /**
   * Create test file.
   */
  public function setUp() {
    parent::setUp();
    $this->path = tempnam(sys_get_temp_dir(), __FUNCTION__);
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
  protected function callUpdate($rows = [[1]]) {
    $file_info = new SingleFileInfo($this->path, new \DateInterval('PT1H'));
    $file_info->setExporterFactory(ExporterFactoryStub::withRows($rows));
    $file_info->update();
  }

  /**
   * Set mtime of the file 2h in the past so that we refresh the file.
   */
  protected function makeFileStale() {
    touch($this->path, time() - 7200);
    clearstatcache(TRUE, $this->path);
  }

  /**
   * Test that a non-existing file is created.
   */
  public function testUpdateCreatesNewFile() {
    // tempnam() creates the file for security reasons. We want it to not exist.
    unlink($this->path);
    $this->callUpdate();
    $this->assertTrue(file_exists($this->path));
  }

  /**
   * A file is not updated if it was modified within its refresh interval.
   */
  public function testUpdateDoesntUpdateFileWithinRefreshInterval() {
    $this->callUpdate();
    $this->assertTrue(file_exists($this->path));
    $this->assertEqual('', file_get_contents($this->path));
  }

  /**
   * Update the file after the refresh interval has passed.
   */
  public function testUpdateFileAfterRefreshInterval() {
    $this->makeFileStale();
    $this->callUpdate();
    $this->assertTrue(file_exists($this->path));
    $this->assertEqual("1\n", file_get_contents($this->path));
  }

  /**
   * Don’t store partial files on failure.
   */
  public function testNoPartialFilesOnFailure() {
    $file = new \SplFileObject($this->path, 'w');
    $file->fwrite('Original content');
    unset($file);

    $this->makeFileStale();
    try {
      $this->callUpdate([['Partial content'], new \Exception()]);
    }
    catch (\Exception $e) {
      // We expected this.
    }

    $this->assertEqual("Original content", file_get_contents($this->path));
  }

}
