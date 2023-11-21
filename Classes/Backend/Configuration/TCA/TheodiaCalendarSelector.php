<?php
declare(strict_types = 1);

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace Causal\Theodia\Backend\Configuration\TCA;

use Causal\Theodia\Service\TheodiaOrg;
use TYPO3\CMS\Backend\Utility\BackendUtility;

class TheodiaCalendarSelector
{

    /**
     * Returns the list of available theodia calendars.
     *
     * @param array $conf
     * @return array
     */
    public function getAll(array $conf = []): array
    {
        if (empty($conf)) {
            $conf = ['items' => []];
        }

        $record = BackendUtility::getRecord($conf['table'],  (int)($conf['row']['uid'] ?? 0), 'pid');
        $theodiaCalendars = TheodiaOrg::getTheodiaCalendarsForTca($record['pid'] ?? 0);
        // Drop empty item at the beginning
        array_shift($theodiaCalendars);

        $conf['items'] = array_merge(
            $conf['items'], $theodiaCalendars
        );

        return $conf;
    }

}