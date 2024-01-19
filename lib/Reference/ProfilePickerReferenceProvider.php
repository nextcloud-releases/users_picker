<?php

declare(strict_types=1);

namespace OCA\UsersPicker\Reference;

use OC\Collaboration\Reference\LinkReferenceProvider;
use OCP\Collaboration\Reference\ADiscoverableReferenceProvider;
use OCP\Collaboration\Reference\Reference;

use OCP\Collaboration\Reference\IReference;
use OCP\Contacts\IManager;
use OCP\IL10N;
use OCP\IURLGenerator;

use OCA\UsersPicker\AppInfo\Application;
use OCP\Accounts\IAccountManager;
use OCP\IUserManager;
use Psr\Log\LoggerInterface;

class ProfilePickerReferenceProvider extends ADiscoverableReferenceProvider {

	private const RICH_OBJECT_TYPE = Application::APP_ID . '_profile';

	private ?string $userId;
	private IL10N $l10n;
	private IURLGenerator $urlGenerator;
	private LinkReferenceProvider $linkReferenceProvider;
	private IUserManager $userManager;
	private IAccountManager $accountManager;
	private IManager $contactsManager;

	public function __construct(
		IL10N $l10n,
		IURLGenerator $urlGenerator,
		LinkReferenceProvider $linkReferenceProvider,
		IUserManager $userManager,
		IAccountManager $accountManager,
		IManager $contactsManager,
		?string $userId,
		private LoggerInterface $logger,
	) {
		$this->userId = $userId;
		$this->l10n = $l10n;
		$this->urlGenerator = $urlGenerator;
		$this->linkReferenceProvider = $linkReferenceProvider;
		$this->userManager = $userManager;
		$this->accountManager = $accountManager;
		$this->contactsManager = $contactsManager;
	}

	/**
	 * @inheritDoc
	 */
	public function getId(): string	{
		return 'profile_picker';
	}

	/**
	 * @inheritDoc
	 */
	public function getTitle(): string {
		return $this->l10n->t('Profile picker');
	}

	/**
	 * @inheritDoc
	 */
	public function getOrder(): int	{
		return 10;
	}

	/**
	 * @inheritDoc
	 */
	public function getIconUrl(): string {
		return $this->urlGenerator->imagePath(Application::APP_ID, 'app-dark.svg');
	}

	/**
	 * @inheritDoc
	 */
	public function matchReference(string $referenceText): bool {
		return $this->getObjectId($referenceText) !== null;
	}

	/**
	 * @inheritDoc
	 */
	public function resolveReference(string $referenceText): ?IReference {
		if (!$this->matchReference($referenceText)) {
			return null;
		}

		$userId = $this->getObjectId($referenceText);
		$user = $this->userManager->get($userId);
		$contacts = $this->contactsManager->search($userId, ['UID', 'FN', 'EMAIL'], ['types' => true]);
		$this->logger->error('Contancts: ' . json_encode($contacts));

		if ($user === null) {
			return $this->linkReferenceProvider->resolveReference($referenceText);
		}

		$reference = new Reference($referenceText);

		$userDisplayName = $user->getDisplayName();
		$userEmail = $user->getEMailAddress();
		$userAvatarUrl = $this->urlGenerator->linkToRouteAbsolute('core.avatar.getAvatar', ['userId' => $userId, 'size' => '64']);

		$bio = $this->accountManager->getAccount($user)->getProperty(IAccountManager::PROPERTY_BIOGRAPHY);
		$bio = $bio->getScope() !== IAccountManager::SCOPE_PRIVATE ? $bio->getValue() : null;
		$headline = $this->accountManager->getAccount($user)->getProperty(IAccountManager::PROPERTY_HEADLINE);
		$location = $this->accountManager->getAccount($user)->getProperty(IAccountManager::PROPERTY_ADDRESS);
		$website = $this->accountManager->getAccount($user)->getProperty(IAccountManager::PROPERTY_WEBSITE);
		$organisation = $this->accountManager->getAccount($user)->getProperty(IAccountManager::PROPERTY_ORGANISATION);
		$role = $this->accountManager->getAccount($user)->getProperty(IAccountManager::PROPERTY_ROLE);

		// for clients who can't render the reference widgets
		$reference->setTitle($userDisplayName);
		$reference->setDescription($userEmail ?? $userDisplayName);
		$reference->setImageUrl($userAvatarUrl);

		// for the Vue reference widget
		$reference->setRichObject(
			self::RICH_OBJECT_TYPE,
			[
				'user_id' => $userId,
				'title' => $userDisplayName,
				'subline' => $userEmail ?? $userDisplayName,
				'email' => $userEmail,
				'bio' => isset($bio) && $bio !== '' ? substr_replace($bio, '...', 80, strlen($bio)) : null,
				'headline' => $headline->getScope() !== IAccountManager::SCOPE_PRIVATE ? $headline->getValue() : null,
				'location' => $location->getScope() !== IAccountManager::SCOPE_PRIVATE ? $location->getValue() : null,
				'location_url' => $location->getScope() !== IAccountManager::SCOPE_PRIVATE ? $this->getOpenStreetLocationUrl($location->getValue()) : null,
				'website' => $website->getScope() !== IAccountManager::SCOPE_PRIVATE ? $website->getValue() : null,
				'organisation' => $organisation->getScope() !== IAccountManager::SCOPE_PRIVATE ? $organisation->getValue() : null,
				'role' => $role->getScope() !== IAccountManager::SCOPE_PRIVATE ? $role->getValue() : null,
				'url' => $referenceText,
			]
		);
		return $reference;
	}

	private function getObjectId(string $url): ?string {
		$baseUrl = $this->urlGenerator->getBaseUrl();
		$baseWithIndex = $baseUrl . '/index.php';

		preg_match('/^' . preg_quote($baseUrl, '/') . '\/u\/(\w+)$/', $url, $matches);
		if (count($matches) > 1) {
			return $matches[1];
		}
		preg_match('/^' . preg_quote($baseWithIndex, '/') . '\/u\/(\w+)$/', $url, $matches);
		if (count($matches) > 1) {
			return $matches[1];
		}

		return null;
	}

	private function getOpenStreetLocationUrl($location) {
		return 'https://www.openstreetmap.org/search?query=' . urlencode($location);
	}

	/**
	 * @inheritDoc
	 */
	public function getCachePrefix(string $referenceId): string {
		return $this->userId ?? '';
	}

	/**
	 * @inheritDoc
	 */
	public function getCacheKey(string $referenceId): ?string {
		$objectId = $this->getObjectId($referenceId);
		if ($objectId !== null) {
			return $objectId;
		}
		return $referenceId;
	}
}
