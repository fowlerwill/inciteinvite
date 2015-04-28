<?php

require_once( 'inciteinvite.php' );

class InciteInvite_Tests extends WP_UnitTestCase {

	private $plugin;
	
	private $teamNameField 		= 'iiteam_name';
	private $teamManagerField 	= 'iiteam_manager';
	private $teamMemberField 	= 'iiteam_member';
	private $teamPostType 		= 'iiteam';

	function setUp() {

		parent::setUp();
		$this->plugin = $GLOBALS['inciteinvite'];
		$this->assertTrue(activate_inciteinvite(), 'Adding Roles failed');

	} // end setup

	function tearDown() {
		parent::tearDown();
		$this->assertTrue(deactivate_inciteinvite(), 'Removing Roles failed');
	}

	function testPluginInitialization() {
		$this->assertFalse( null == $this->plugin );
	} // end testPluginInitialization

	public function testRolesExist() {
		$this->assertInstanceOf('WP_Role', get_role( $this->teamManagerField ), 'Manager role not registered');
		$this->assertInstanceOf('WP_Role', get_role( $this->teamMemberField ), 'Member role not registered');
	}

	public function testAddAndRemoveManagerUser() {
		$user = $this->addUserByRole($this->teamManagerField);
		$this->assertTrue(wp_delete_user($user->ID), "Could not Delete User");
	}

	public function testAddAndRemoveMemberUser() {
		$user = $this->addUserByRole($this->teamMemberField);
		$this->assertTrue(wp_delete_user($user->ID), "Could not Delete User");
	}

	public function testTeamCustomPostTypeRegistered() {
		$results = get_post_types();
		$this->assertContains($this->teamPostType, $results, 'Team not registered');
	}

	public function addNewTeamManagerUser() {
		$post = $this->factory->user->create_and_get(['roles' => [$this->teamManagerField]]);
	}

	public function testRegistrationIfTeamExists() {

		$_SERVER['SERVER_NAME'] = 'testserver';

		$_POST[$this->teamNameField] = 'HRBulls';
		$useraID = $this->registerUser('a');

		$this->assertTrue(get_user_meta($useraID, $this->teamNameField, true) == 'HRBulls', 'User team name not saved');



		$_POST[$this->teamNameField] = 'HRBulls';
		$userbID = $this->registerUser('b');

		$this->assertInstanceOf('WP_Error', $userbID, 'User B was registered with same team name');

	}

	/**
	 * Should only be able to add a team when a user is registered in v1
	 */
	public function testAddingSingleTeam() {
		$_POST[$this->teamNameField] = 'A Brand New Team';
		$user_id = $this->registerUser('abnt');
		$page = get_page_by_title($_POST[$this->teamNameField], OBJECT, $this->teamPostType);
		$this->assertInstanceOf('WP_Post', $page, 'Team page not created');
	}

    /**
     * Should only be able to add a team when a user is registered in v1
     */
    public function testExtentsTeamNames() {

        //one
        $_POST[$this->teamNameField] = "o";
        $user_id = $this->registerUser('2');
        $this->assertFalse(is_wp_error($user_id), 'Unable to make single digit team');

        //Many (101 chars)
        $_POST[$this->teamNameField] = '';
        for( $i = 0; $i < 100; $i++) {
            $_POST[$this->teamNameField] .= $i%10;
        }
        $user_id = $this->registerUser('3');
        $this->assertTrue(is_wp_error($user_id), 'Huge team name was created: ' . $_POST[$this->teamNameField]);

        // legal symbols
        $_POST[$this->teamNameField] = "-_@.!$#";
        $user_id = $this->registerUser('4');
        $this->assertFalse(is_wp_error($user_id), 'unable to register legal symbol team name');

        // illegal symbols
        $_POST[$this->teamNameField] = "~%^*()";
        $user_id = $this->registerUser('5');
        $this->assertTrue(is_wp_error($user_id), 'registered an illegal symbol team name');

    }

    /**
     * Test updating a team
     */
    public function testUpdatingTeamExtents() {
        $_POST[$this->teamNameField] = 'theabcs';
        $user_id = $this->registerUser('1');

        $page = get_page_by_title($_POST[$this->teamNameField], OBJECT, $this->teamPostType);

        $_POST = [];

        // update to new name
        $_POST['teamid']                = $page->ID;
        $_POST['iiteam_name']           = 'thedefs';
        $_POST['iiteam_description']    = 'a Description';
        $_POST['iiteam_timezone']       = 'America/Edmonton';
        $_REQUEST['_wpnonce'] = $_POST['_wpnonce'] = wp_create_nonce('edit_iiteam_'.$page->ID);
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $team = new InciteInvite_Team();

        $team->update_team($user_id);

        $this->assertTrue(get_the_title($page->ID) == 'thedefs', 'Team name not updated');

        // update to bad timezone
        $_POST['teamid']                = $page->ID;
        $_POST['iiteam_name']           = 'thedefs';
        $_POST['iiteam_description']    = 'a Description';
        $_POST['iiteam_timezone']       = 'Canada/Edmonton';
        $_REQUEST['_wpnonce'] = $_POST['_wpnonce'] = wp_create_nonce('edit_iiteam_'.$page->ID);
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $team = new InciteInvite_Team();

        $team->update_team($user_id);

        $this->assertFalse(get_post_meta($page->ID, 'iiteam_timezone', true) == 'Canada/Edmonton', call_user_func(function() use ($page) {
            return "Bad timezone saved\nExpected Result: " . print_r('America/Edmonton')
            . "\nActual Result: " . print_r(get_post_meta($page->ID, 'iiteam_timezone', true));
        }) );

    }

	/**
	 * Should delete teams when users are deleted
	 * Deleting a user seems impossible programatically using PHPUnit & WP_UnitTestCase
	 * Cannot modify the headers & cannot log  a user in because of that.
	 */
	public function testDeletingUserAndTeam() {
//		$_POST[$this->teamNameField] = 'Delteam';
//		$user_id = $this->registerUser('dt');
//		$userObj = get_user_by('id', $user_id);
//		reset_password( $userObj, 'test' );
//		$usercred = ['user_login' => 'userdt', 'user_password' => 'test'];
//
//		echo var_dump(print_r($_SERVER));
//
////		$user = wp_signon($usercred);
////		if( is_wp_error($user) ) {
////			echo $user->get_error_message();
////		}
//
//		$_GET['DelAcct'] = 'yes';
//
//		echo var_dump($user);
	}


	/**
	 * ********************************** Utility Methods
	 */

	private function registerUser($userNumber) {
        ( empty($_POST['iiteam_name']) ) ? $_POST['iiteam_name'] = wp_generate_password() : $_POST['iiteam_name'];
		$_POST['user_login'] = 'user' . $userNumber;
		$_POST['user_email'] = 'user' . $userNumber . '@email.com';
		return register_new_user($_POST['user_login'], $_POST['user_email']);
	}

	private function addUserByRole($role) {
        ( empty($_POST['iiteam_name']) ) ? $_POST['iiteam_name'] = wp_generate_password() : $_POST['iiteam_name'];
		$user = $this->factory->user->create_and_get(['role' => $role]);
		$this->assertInstanceOf('WP_User', $user, $role . ' unable to be created');
		$this->assertContains($role, $user->roles, 'User is not a ' . $role);
		return $user;
	}

} // end class
