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

namespace OCA\UsersPicker\Service;

use OCP\Collaboration\Collaborators\ISearch;

class UsersPickerService {
	/** @var ISearch */
	private $search;

	public function __construct(ISearch $search) {
		$this->search = $search;
	}

	public function searchProfiles(string $userId, string $term, int $offset, int $limit): array {
		$shareTypes[] = 0;
		$searchUsersResult = $this->search->search($term, $shareTypes, false, $limit, $offset);
		return [
			'results' => $searchUsersResult[0],
			'has_more' => $searchUsersResult[1]
		];
	}
}
