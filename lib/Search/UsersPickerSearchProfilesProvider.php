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

namespace OCA\UsersPicker\Search;

use OCP\App\IAppManager;
use OCP\IL10N;
use OCP\IConfig;
use OCP\IURLGenerator;
use OCP\IUser;
use OCP\Search\IProvider;
use OCP\Search\ISearchQuery;
use OCP\Search\SearchResult;

use OCA\UsersPicker\AppInfo\Application;
use OCA\UsersPicker\Service\NotionAPIService;
use OCA\UsersPicker\Service\UsersPickerService;

class UsersPickerSearchProfilesProvider implements IProvider {
	/** @var IAppManager */
	private $appManager;

	/** @var IL10N */
	private $l10n;

	/** @var IURLGenerator */
	private $urlGenerator;
	/**
	 * @var IConfig
	 */
	private $config;
	/**
	 * @var NotionAPIService
	 */
	private $service;

	public function __construct(
		IAppManager $appManager,
		IL10N $l10n,
		IConfig $config,
		IURLGenerator $urlGenerator,
		UsersPickerService $service,
	) {
		$this->appManager = $appManager;
		$this->l10n = $l10n;
		$this->config = $config;
		$this->urlGenerator = $urlGenerator;
		$this->service = $service;
	}

	/**
	 * @inheritDoc
	 */
	public function getId(): string {
		return 'users-picker-profiles';
	}

	/**
	 * @inheritDoc
	 */
	public function getName(): string {
		return $this->l10n->t('User profiles');
	}

	/**
	 * @inheritDoc
	 */
	public function getOrder(string $route, array $routeParameters): int {
		return 20;
	}

	/**
	 * @inheritDoc
	 */
	public function search(IUser $user, ISearchQuery $query): SearchResult {
		if (!$this->appManager->isEnabledForUser(Application::APP_ID, $user)) {
			return SearchResult::complete($this->getName(), []);
		}

		$limit = $query->getLimit();
		$term = $query->getTerm();
		$offset = $query->getCursor();
		$offset = isset($offset) && $offset !== 0 ? $offset : 0;

		// $searchPagesEnabled = $this->config->getUserValue($user->getUID(), Application::APP_ID, 'search_profiles_enabled', '0') === '1';

		// if (!$searchPagesEnabled) {
		// 	return SearchResult::paginated($this->getName(), [], 0);
		// }

		$searchResult = $this->service->searchProfiles($user->getUID(), $term, $offset, $limit);
		if (isset($searchResult['error'])) {
			$pages = [];
		} elseif (isset($searchResult['results']['exact']['users'])) {
			$pages = $searchResult['results']['exact']['users'];
		}

		$formattedResults = array_map(function (array $entry): UsersPickerSearchResultEntry {
			return new UsersPickerSearchResultEntry(
				$this->urlGenerator->imagePath(Application::APP_ID, 'account.svg'),
				$entry['label'],
				$entry['shareWithDisplayNameUnique'],
				$this->getUserProfileLink($entry),
				'icon-user',
				false,
			);
		}, $pages);

		// if (isset($searchResult['has_more']) && $searchResult['has_more']) {
		// 	return SearchResult::paginated(
		// 		$this->getName(),
		// 		$formattedResults,
		// 		isset($searchResult['has_more']) && $searchResult['has_more']
		// 			? $searchResult['next_cursor'] : 0
		// 	);
		// }
		return SearchResult::complete(
			$this->getName(),
			$formattedResults
		);
	}

	public function getUserProfileLink(array $entry): string {
		return $this->urlGenerator->getBaseUrl() . '/u/' . $entry['label'];
	}

}
