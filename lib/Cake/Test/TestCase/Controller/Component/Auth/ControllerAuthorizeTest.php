<?php
/**
 * ControllerAuthorizeTest file
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2011, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2011, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       Cake.Test.Case.Controller.Component.Auth
 * @since         CakePHP(tm) v 2.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
namespace Cake\Test\TestCase\Controller\Component\Auth;
use \Cake\TestSuite\TestCase,
	\Cake\Controller\Component\Auth\ControllerAuthorize,
	\Cake\Controller\Controller,
	\Cake\Network\Request;

class ControllerAuthorizeTest extends TestCase {

/**
 * setup
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->controller = $this->getMock('Cake\Controller\Controller', array('isAuthorized'), array(), '', false);
		$this->components = $this->getMock('Cake\Controller\ComponentCollection');
		$this->components->expects($this->any())
			->method('getController')
			->will($this->returnValue($this->controller));

		$this->auth = new ControllerAuthorize($this->components);
	}

/**
 *
 * @expectedException \Cake\Error\Exception
 */
	public function testControllerTypeError() {
		$this->auth->controller(new \StdClass());
	}

/**
 * @expectedException \Cake\Error\Exception
 */
	public function testControllerErrorOnMissingMethod() {
		$this->auth->controller(new Controller());
	}

/**
 * test failure
 *
 * @return void
 */
	public function testAuthorizeFailure() {
		$user = array();
		$request = new Request('/posts/index', false);
		$this->assertFalse($this->auth->authorize($user, $request));
	}

/**
 * test isAuthorized working.
 *
 * @return void
 */
	public function testAuthorizeSuccess() {
		$user = array('User' => array('username' => 'mark'));
		$request = new Request('/posts/index', false);

		$this->controller->expects($this->once())
			->method('isAuthorized')
			->with($user)
			->will($this->returnValue(true));

		$this->assertTrue($this->auth->authorize($user, $request));
	}
}