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

namespace Causal\Theodia\Event;

final class AfterEventsFetchedEvent
{
    public function __construct(
        private readonly array $calendars,
        private array $events
    ) {}
    
    public function getCalendars(): array
    {
        return $this->calendars;
    }
    
    public function getEvents(): array
    {
        return $this->events;
    }
    
    public function setEvents(array $events): void
    {
        $this->events = $events;
    }
}
