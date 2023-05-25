<?php

namespace App\Helpers;

use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use Carbon\Carbon;

class Auth {

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
			if (true !== Hash::check($password, $user->password)) {
				throw new Exception('Invalid password');
			}

			// OK, user's credentials are OK
			session()->put('username', $username);
			session()->put('authenticated', true);

			$user->connected_at = Carbon::now();
			$user->save();

			return true;
		}
		catch (Exception $e) {
			throw $e;
		}
	}

	static function getLoggedUserDetails():User {
		if (self::isLogged()) {
			return self::getUserDetails(session()->get('username'));
		}
		throw new UnauthenticatedUser('User is not logged in');
	}

	static function getUserDetails(String $username):User {
		$user = User::find($username);
		if (empty($user)) {
			throw new Exception('No such user');
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

	static function logout() {
		if (self::isLogged()) {
			session()->invalidate();
		}
	}
}


class UnauthenticatedUser extends Exception {}

?>
