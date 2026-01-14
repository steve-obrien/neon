<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2017 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @author Steve O'Brien <steve.obrien@newicon.net> 25/04/2020
 * @package neon
 *
 * This is the view for the global access password. This is not meant as a fall proof security measure.
 * It is intended to be used for demoing soon to go live websites and apps to key people
 */

?>
<!doctype html>
<html lang="en">
<head>
	<meta content="summary" name="twitter:card">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<meta charset="utf-8"/>
	<title>Password</title>
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tailwindcss/ui@latest/dist/tailwind-ui.min.css">
	<style>
		@keyframes shake {
			0% {
				transform: translate(0);
			}
			20% {
				transform: translate(3em);
			}
			40% {
				transform: translate(-3em);
			}
			60% {
				transform: translate(3em);
			}
			80% {
				transform: translate(-3em);
			}
			100% {
				transform: translate(0);
			}
		}
		.shake {
			animation: shake 0.5s;
		}
	</style>
</head>
<body>

<div class="fixed bottom-0 inset-x-0 px-4 pb-6 sm:inset-0 sm:p-0 sm:flex sm:items-center sm:justify-center">
	<div class="fixed inset-0 transition-opacity">
		<div class="absolute inset-0 bg-gray-500 opacity-75"></div>
	</div>

	<div class="bg-white <?= $incorrectPassword?'shake':'' ?> rounded-lg px-4 pt-5 pb-4 overflow-hidden shadow-xl transform transition-all sm:max-w-sm sm:w-full sm:p-6">
		<div>
<!--			<div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100">-->
<!--				<svg class="h-6 w-6 text-yellow-600" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>-->
<!--			</div>-->
<!--			<div class="mt-3 text-center sm:mt-5">-->
<!--				<h3 class="text-lg leading-6 font-medium text-gray-900">-->
<!--					Password-->
<!--				</h3>-->
<!--				<div class="mt-2">-->
<!--					<p class="text-sm leading-5 text-gray-500">-->
<!--						Enter the password to gain access-->
<!--					</p>-->
<!--				</div>-->
				<form class="m-0 flex">
					<input class="flex-grow shadow-inner form-input focus:z-10 rounded-none rounded-l-md" type="password" name="<?= $passwordParam ?>" placeholder="Enter Password" />
					<button class="w-18 -ml-px rounded-none rounded-r-md inline-flex py-3 justify-center border border-gray-300 px-4 py-2 text-base leading-6 font-medium text-black hover:bg-gray-100 focus:outline-none focus:shadow-outline-blue transition ease-in-out duration-150 sm:text-sm sm:leading-5" type="submit">
						<svg class="align-end  h-6 w-6" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" stroke="currentColor" viewBox="0 0 24 24"><path d="M13 9l3 3m0 0l-3 3m3-3H8m13 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
					</button>
				</form>
				<?php if ($incorrectPassword): ?>
					<p class="text-red-500 inline-flex mt-2">
						<svg class="h-5 w-5 mr-1" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" stroke="currentColor" viewBox="0 0 24 24"><path d="M8 7l4-4m0 0l4 4m-4-4v18"></path></svg>
						Incorrect password
					</p>
				<?php endif ?>
			</div>
<!--		</div>-->
	</div>
</div>
</body>
</html>