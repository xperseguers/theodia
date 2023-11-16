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

namespace Causal\Theodia\Tca;

use Causal\Theodia\Service\Theodia;

class TheodiaCalendarSelector
{

    /**
     * Returns the list of available Theodia calendars.
     *
     * @param array $conf
     * @param \TYPO3\CMS\Backend\Form\FormDataProvider\TcaSelectItems $pObj
     * @return array
     */
    public function getAll(array $conf = [], $pObj): array
    {
        if (empty($conf)) {
            $conf = ['items' => []];
        }

        $theodiaCalendars = Theodia::getTheodiaCalendarsForTca();
        // Drop empty item at the beginning
        array_shift($theodiaCalendars);

        $conf['items'] = array_merge(
            $conf['items'], $theodiaCalendars
        );

        return $conf;
    }

}