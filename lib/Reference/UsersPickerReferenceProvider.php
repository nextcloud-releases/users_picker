<?php
/**
 * @copyright Copyright (c) 2023 Andrey Borysenko <andrey18106x@gmail.com>
 *
 * @copyright Copyright (c) 2023 Alexander Piskun <bigcat88@icloud.com>
 *
 * @author 2023 Andrey Borysenko <andrey18106x@gmail.com>
 *
 * @license AGPL-3.0-or-later
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\UsersPicker\Reference;

use OC\Collaboration\Reference\LinkReferenceProvider;
use OCP\Collaboration\Reference\ADiscoverableReferenceProvider;
use OCP\Collaboration\Reference\Reference;

use OCP\Collaboration\Reference\IReference;
use OCP\IL10N;
use OCP\IURLGenerator;

use OCA\UsersPicker\AppInfo\Application;
use OCP\IUserManager;

class UsersPickerReferenceProvider extends ADiscoverableReferenceProvider {

	private const RICH_OBJECT_TYPE = Application::APP_ID . '_profile';

	private ?string $userId;
	private IL10N $l10n;
	private IURLGenerator $urlGenerator;
	private LinkReferenceProvider $linkReferenceProvider;
	private IUserManager $userManager;

	public function __construct(
		IL10N $l10n,
		IURLGenerator $urlGenerator,
		LinkReferenceProvider $linkReferenceProvider,
		IUserManager $userManager,
		?string $userId
	) {
		$this->userId = $userId;
		$this->l10n = $l10n;
		$this->urlGenerator = $urlGenerator;
		$this->linkReferenceProvider = $linkReferenceProvider;
		$this->userManager = $userManager;
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
		return $this->l10n->t('User profiles');
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
		if (preg_match('/^https:\/\/[\w\-.]+(:\d+)?\/\S+\/u\/\w+$/i', $referenceText) === 1) {
			return true;
		}
		return false;
	}

	/**
	 * @inheritDoc
	 */
	public function resolveReference(string $referenceText): ?IReference {
		if ($this->matchReference($referenceText)) {
			$userId = $this->getObjectId($referenceText);
			$user = $this->userManager->get($userId);
			if ($user !== null) {
				$reference = new Reference($referenceText);
				$reference->setRichObject(
					self::RICH_OBJECT_TYPE,
					[
						'user_id' => $userId,
						'title' => $user->getDisplayName(),
						'subline' => $user->getDisplayName(),
						'email' => 	$user->getEMailAddress(),
						'thumbnail_url' => null,
						'url' => $referenceText,
					]
				);
				return $reference;
			}
			return $this->linkReferenceProvider->resolveReference($referenceText);
		}
		return null;
	}

	private function getObjectId(string $url): ?string {
		$userId = explode('/u/', $url)[1];
		return $userId;
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
