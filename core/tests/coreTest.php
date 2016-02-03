<?php
require_once '../../pgproc/php/pgprocedures.php';
require_once '../../config.inc.php';

class coreTest extends PHPUnit_Framework_TestCase {
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

  protected function assertPreConditions()
  {
    //    echo "\n".'*** pre conditions'."\n";
    self::$base->startTransaction();
  }

  protected function assertPostConditions()
  {
    //    echo "\n".'*** post conditions'."\n";
    self::$base->rollback();
  }

  /**
   * Valid authentication 
   */
  public function testUserLoginOk() {
    $login = 'testdejfhcqcsdfkhn';
    $pwd = 'ksfdjgsfdyubg';    
    self::$base->execute_sql("insert into login.user (usr_login, usr_salt, usr_right_structure, usr_right_config) values ('".$login."', pgcrypto.crypt('".$pwd."', pgcrypto.gen_salt('bf', 8)), true, false);");

    $res = self::$base->login->user_login($login, $pwd);
    $this->assertGreaterThan(0, $res['usr_token']);
    $this->assertFalse($res['usr_temp_pwd']);
    $this->assertTrue($res['usr_right_structure']);
    $this->assertFalse($res['usr_right_config']);
  }
  
  /**
   * Login exception
   * @expectedException PgProcException
   */
  public function testUserLoginException() {
    $login = 'testdejfhcqcsdfkhn';
    $pwd = 'ksfdjgsfdyubg';    
    self::$base->execute_sql("insert into login.user(usr_login, usr_salt, usr_right_structure, usr_right_config) values ('".$login."', pgcrypto.crypt('".$pwd."', pgcrypto.gen_salt('bf', 8)), true, false);");

    $res = self::$base->login->user_login($login, $pwd."X");
  }


  /**
   * Test user logout
   */
  public function testUserLogoutOk() {
    $login = 'testdejfhcqcsdfkhn';
    $pwd = 'ksfdjgsfdyubg';    
    self::$base->execute_sql("insert into login.user (usr_login, usr_salt, usr_right_structure, usr_right_config) values ('".$login."', pgcrypto.crypt('".$pwd."', pgcrypto.gen_salt('bf', 8)), true, false);");

    $res = self::$base->login->user_login($login, $pwd);
    $this->assertGreaterThan(0, $res['usr_token']);

    self::$base->login->user_logout($res['usr_token']);

    // Token should be invalid now
    $this->setExpectedException('PgProcException');
    self::$base->login->user_logout($res['usr_token']);
  }

  /**
   * Test password change
   */
  public function testUserChangePassword() {
    $login = 'testdejfhcqcsdfkhn';
    $pwd = 'ksfdjgsfdyubg';    
    $newpwd = 'sdfjkgh';    
    self::$base->execute_sql("insert into login.user (usr_login, usr_salt, usr_right_structure, usr_right_config) values ('".$login."', pgcrypto.crypt('".$pwd."', pgcrypto.gen_salt('bf', 8)), true, false);");

    $res = self::$base->login->user_login($login, $pwd);
    $this->assertGreaterThan(0, $res['usr_token']);

    self::$base->login->user_change_password($res['usr_token'], $newpwd);
    self::$base->login->user_logout($res['usr_token']);

    $res = self::$base->login->user_login($login, $newpwd);
    $this->assertGreaterThan(0, $res['usr_token']);
    $this->assertFalse($res['usr_temp_pwd']);
    
    self::$base->login->user_logout($res['usr_token']);
    $this->setExpectedException('PgProcException');
    $res = self::$base->login->user_login($login, 'wrong_pwd');
  }

  /**
   * Test password regenerate
   */
  public function testUserRegeneratePassword() {
    $loginAdmin = 'admin';
    $pwdAdmin = 'ksfdjgsfdyubg';    
    
    $loginLost = 'toto';
    $pwdLost = 'tata';

    self::$base->execute_sql("insert into login.user (usr_login, usr_salt, usr_right_structure, usr_right_config) values ('".$loginAdmin."', pgcrypto.crypt('".$pwdAdmin."', pgcrypto.gen_salt('bf', 8)), false, true);");

    self::$base->execute_sql("insert into login.user (usr_login, usr_salt, usr_right_structure, usr_right_config) values ('".$loginLost."', pgcrypto.crypt('".$pwdLost."', pgcrypto.gen_salt('bf', 8)), true, false);");

    $admin = self::$base->login->user_login($loginAdmin, $pwdAdmin);
    $this->assertGreaterThan(0, $admin['usr_token']);

    $toto = self::$base->login->user_login($loginLost, $pwdLost);
    $this->assertGreaterThan(0, $toto['usr_token']);
    self::$base->login->user_logout($toto['usr_token']);
    
    $tmppwd = self::$base->login->user_regenerate_password($admin['usr_token'], $loginLost);

    $toto2 = self::$base->login->user_login($loginLost, $tmppwd);
    $this->assertGreaterThan(0, $toto2['usr_token']);
    $this->assertTrue($toto2['usr_temp_pwd']);
    self::$base->login->user_logout($toto2['usr_token']);
    
  }

  /**
   * Test password regenerate on same user
   */
  public function testUserRegenerateMyPassword() {
    $loginAdmin = 'admin';
    $pwdAdmin = 'ksfdjgsfdyubg';    
    
    self::$base->execute_sql("insert into login.user (usr_login, usr_salt, usr_right_structure, usr_right_config) values ('".$loginAdmin."', pgcrypto.crypt('".$pwdAdmin."', pgcrypto.gen_salt('bf', 8)), false, true);");

    $admin = self::$base->login->user_login($loginAdmin, $pwdAdmin);
    $this->assertGreaterThan(0, $admin['usr_token']);
    
    $this->setExpectedException('PgProcException');
    $tmppwd = self::$base->login->user_regenerate_password($admin['usr_token'], $loginAdmin);    
  }

}

?>
