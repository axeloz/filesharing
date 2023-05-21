<?php

namespace App\Helpers;

use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class User {

	static function isLogged():Bool {
		// Checking credentials auth
		if (session()->get('authenticated', false) === true && session()->has('username')) {
			// If user still exists
			try {
				self::getUserDetails(session()->get('username'));
				return true;
			}
			catch (Exception $e) {}
		}
		return false;
	}

	static function loginUser(String $username, String $password):Bool {
		try {
			// Checking user existence
			$user = self::getUserDetails($username);

			// Checking password
			if (true !== Hash::check($password, $user['password'])) {
				throw new Exception('Invalid password');
			}

			// OK, user's credentials are OK
			session()->put('username', $username);
			session()->put('authenticated', true);
			return true;
		}
		catch (Exception $e) {
			throw $e;
		}
	}

	static function getLoggedUserDetails():Array {
		if (self::isLogged()) {
			return self::getUserDetails(session()->get('username'));
		}
		throw new UnauthenticatedUser('User is not logged in');
	}

	static function getUserDetails(String $username):Array {

		// Checking user existence
		if (Storage::disk('users')->missing($username.'.json')) {
			throw new Exception('No such user');
		}

		// Getting user.json
		if (! $json = Storage::disk('users')->get($username.'.json')) {
			throw new Exception('Could not fetch user details');
		}

		// Decoding JSON
		if (! $user = json_decode($json, true)) {
			throw new Exception('Cannot decode JSON file');
		}

		return $user;
	}

	static function setUserDetails(String $username, Array $data):Array {
		$original = self::getUserDetails($username);
		$updated = array_merge($original, $data);

		if (Storage::disk('users')->put($username.'.json', json_encode($updated))) {
			return $updated;
		}

		throw new Exception('Could not update user\'s details');
	}
}


class UnauthenticatedUser extends Exception {}

?>
