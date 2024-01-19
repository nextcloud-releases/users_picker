<?php

declare(strict_types=1);

namespace OCA\UsersPicker\AppInfo;

use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\Collaboration\Reference\RenderReferenceEvent;

use OCA\UsersPicker\Listener\UsersPickerReferenceListener;
use OCA\UsersPicker\Reference\ProfilePickerReferenceProvider;

class Application extends App implements IBootstrap {
	public const APP_ID = 'users_picker';

	public function __construct(array $urlParams = []) {
		parent::__construct(self::APP_ID, $urlParams);
	}

	public function register(IRegistrationContext $context): void {
		$context->registerReferenceProvider(ProfilePickerReferenceProvider::class);
		$context->registerEventListener(RenderReferenceEvent::class, UsersPickerReferenceListener::class);
	}

	public function boot(IBootContext $context): void {
	}
}

