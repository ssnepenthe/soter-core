<?php

class Checker_Test extends WP_UnitTestCase {
	/** @test */
	function it_can_check_a_package() {
		$checker = $this->make_checker();
		$package = new Soter_Core\Package( 'contact-form-7', 'plugin', '3.5' );

		$vulns = $checker->check_package( $package );

		$this->assertEqualSets(
			array(
				'Contact Form 7 <= 3.7.1 - Security Bypass',
				'Contact Form 7 <= 3.5.2 - File Upload Remote Code Execution',
			),
			wp_list_pluck( $vulns, 'title' )
		);
	}

	/** @test */
	function it_can_check_multiple_packages() {
		$checker = $this->make_checker();
		$packages = array(
			new Soter_Core\Package( 'contact-form-7', 'plugin', '3.7' ),
			new Soter_Core\Package( 'twentyfifteen', 'theme', '1.1' ),
		);

		$vulns = $checker->check_packages( $packages );

		$this->assertEqualSets(
			array(
				'Contact Form 7 <= 3.7.1 - Security Bypass',
				'Twenty Fifteen Theme <= 1.1 - DOM Cross-Site Scripting (XSS)',
			),
			wp_list_pluck( $vulns, 'title' )
		);
	}

	/** @test */
	function it_can_ignore_some_packages_when_checking_many() {
		$checker = $this->make_checker();
		$packages = array(
			new Soter_Core\Package( 'contact-form-7', 'plugin', '3.7' ),
			new Soter_Core\Package( 'twentyfifteen', 'theme', '1.1' ),
		);

		$vulns = $checker->check_packages( $packages, array( 'twentyfifteen' ) );

		$this->assertEqualSets(
			array( 'Contact Form 7 <= 3.7.1 - Security Bypass' ),
			wp_list_pluck( $vulns, 'title' )
		);
	}

	/** @test */
	function it_only_returns_unique_vulnerabilities() {
		$checker = $this->make_checker();
		$packages = array(
			new Soter_Core\Package( 'contact-form-7', 'plugin', '3.5' ),
			new Soter_Core\Package( 'contact-form-7', 'plugin', '3.7' ),
		);

		$vulns = $checker->check_packages( $packages );

		$this->assertEqualSets(
			array(
				'Contact Form 7 <= 3.5.2 - File Upload Remote Code Execution',
				'Contact Form 7 <= 3.7.1 - Security Bypass',
			),
			wp_list_pluck( $vulns, 'title' )
		);
	}

	/** @test */
	function it_provides_access_to_api_client() {
		$checker = $this->make_checker();

		$this->assertInstanceOf( 'Soter_Core\\Api_Client', $checker->get_client() );
	}

	/** @test */
	function it_can_check_plugin_theme_and_wordpress_types() {
		$checker = $this->make_checker();
		$packages = array(
			new Soter_Core\Package( 'contact-form-7', 'plugin', '3.7' ),
			new Soter_Core\Package( 'twentyfifteen', 'theme', '1.1' ),
			new Soter_Core\Package( '474', 'wordpress', '4.7.4' ),
		);

		$vulns = $checker->check_packages( $packages );

		$this->assertEqualSets(
			array(
				'Contact Form 7 <= 3.7.1 - Security Bypass',
				'Twenty Fifteen Theme <= 1.1 - DOM Cross-Site Scripting (XSS)',
				'WordPress 2.3-4.7.4 - Host Header Injection in Password Reset',
			),
			wp_list_pluck( $vulns, 'title' )
		);
	}

	protected function make_checker() {
		$http = new Filesystem_Http_Client;
		$cache = new Null_Cache;
		$client = new Soter_Core\Api_Client( $http, $cache );

		return new Soter_Core\Checker( $client );
	}
}
