<?php
require_once '../../pgproc/php/pgprocedures.php';
require_once '../../config.inc.php';

class pgdocTest extends PHPUnit_Framework_TestCase {
  private static $base;
  private static $pgHost;
  private static $pgUser;
  private static $pgPass;
  private static $pgDatabase;

  public static function setUpBeforeClass() {

    // Get connection params
    global $pg_host, $pg_user, $pg_pass, $pg_database;
    self::$pgHost = $pg_host;
    self::$pgUser = $pg_user;
    self::$pgPass = $pg_pass;
    self::$pgDatabase = $pg_database;
    self::assertNotNull(self::$pgHost);
    self::assertNotNull(self::$pgUser);
    self::assertNotNull(self::$pgPass);
    self::assertNotNull(self::$pgDatabase);
    
    // Create object
    self::$base = new PgProcedures2 (self::$pgHost, self::$pgUser, self::$pgPass, self::$pgDatabase);
    self::assertNotNull(self::$base);    
  }

  /*********
   * TESTS *
   *********/

  public function testReturnsListOfSchemas() {
    $res = self::$base->pgdoc->list_schemas('pg');    
    $this->assertGreaterThan(0, count($res));
    foreach ($res as $re) {
      $this->assertNotEquals('pg_schema', $re);
    }
  }

  public function testReturnsSchemaDescription() {
    $schema = 'login';
    $res = self::$base->pgdoc->schema_description($schema);
    $this->assertGreaterThan(0, strlen($res));
  }

  public function testReturnsSchemaTables() {
    $schema = 'login';
    $res = self::$base->pgdoc->schema_list_tables($schema);
    print_r($res);
    $this->assertGreaterThan(0, count($res));
  }

  public function testReturnsSchemaTypes() {
    $schema = 'login';
    $res = self::$base->pgdoc->schema_list_types($schema);
    print_r($res);
    $this->assertGreaterThan(0, count($res));
  }

  public function testReturnsSchemaFunctions() {
    $schema = 'login';
    $res = self::$base->pgdoc->schema_list_functions($schema);
    $this->assertGreaterThan(0, count($res));
  }

}
