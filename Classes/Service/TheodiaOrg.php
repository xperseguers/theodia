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

namespace Causal\Theodia\Service;

use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class TheodiaOrg
{
    /**
     * @return array
     */
    public static function getTheodiaCalendars(int $storage): array
    {
        $calendars = [];

        try {
            $site = GeneralUtility::makeInstance(SiteFinder::class)->getSiteByPageId($storage);
        } catch (SiteNotFoundException $e) {
            return $calendars;
        }

        $theodiaCalendars = $site->getConfiguration()['tx_theodia_calendars'] ?? [];
        foreach ($theodiaCalendars as $theodiaCalendar) {
            $id = (int)$theodiaCalendar['id'];
            $calendars[$id] = trim($theodiaCalendar['name']);
        }

        // Sort calendars
        asort($calendars);

        return $calendars;
    }

    public function getEventsByCalendars(array $calendars, int $items = 10, int $cacheLifeTime = 14400): array
    {
        if (empty($calendars)) {
            return [];
        }

        /** @var Site $site */
        $site = $GLOBALS['TYPO3_REQUEST']->getAttribute('site');

        $today = date('Y-m-d');
        sort($calendars);
        $payload = $this->prepareEventsPayload($calendars, $today, $items);
        $cacheKey = 'events_' . implode('-', $calendars) . '_' . $items;
        $data = $this->callApi($payload, $cacheKey, $cacheLifeTime - 10 /* safeguard */);

        $events = $data[0]['data']['events']['items'] ?? [];
        foreach ($events as &$event) {
            if (!($event['cancelled'] ?? false)) {
                // Possibly not semantically "cancelled"
                $event['cancelled'] = preg_match('/annul[ée]/i', $event['name']);
            }
            $event['name'] = mb_eregi_replace('\\s*-?\\s*\\(?annul.e?\\)?\\s*-?\\s*', '', $event['name']);
            foreach (['start', 'end'] as $dateKey) {
                if (!empty($event[$dateKey])) {
                    $date = new \DateTime($event[$dateKey]);
                    $date->setTimezone(new \DateTimeZone('Europe/Zurich'));
                    $event[$dateKey] = $date;
                }
            }

            // Extend with the place of this event
            $placeId = (int)$event['calendar']['place']['id'];
            $event['place'] = $this->getPlace($site, $placeId);

            // Clean-up some types
            $event['id'] = (int)$event['id'];
            $event['cancelled'] = (bool)$event['cancelled'];
            $event['calendar']['id'] = (int)$event['calendar']['id'];
            $event['calendar']['place']['id'] = (int)$event['calendar']['place']['id'];
            $event['calendar']['rite']['id'] = (int)$event['calendar']['rite']['id'];
        }

        return $events;
    }

    protected function getPlace(Site $site, int $id): array
    {
        static $places = [];

        if (!isset($places[$id])) {
            $places[$id] = $this->createAndFetchTheodiaPlace($site, $id);
        }

        return $places[$id];
    }

    protected function callApi(array $payload, string $cacheKey, int $cacheLifeTime): array
    {
        $data = [];
        $cacheDirectory = Environment::getVarPath() . '/transient/';
        $cacheFileName = $cacheDirectory . 'theodia_' . $cacheKey . '.json';
        $useCache = file_exists($cacheFileName);

        if (!$useCache || $GLOBALS['EXEC_TIME'] - filemtime($cacheFileName) > $cacheLifeTime) {
            // Try to or must fetch fresh content
            $url = 'https://theodia.org/graphql?language=fr';
            $headers = [
                'accept: application/json',
                'content-type: application/json',
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            if (!($content = curl_exec($ch))) {
                $useCache = file_exists($cacheFileName);
            } else {
                $data = json_decode($content, true) ?? [];
                if (!isset($data['error'])) {
                    GeneralUtility::writeFile($cacheFileName, $content);
                }
            }
            curl_close($ch);
        }

        if ($useCache) {
            $content = file_get_contents($cacheFileName);
            $data = json_decode($content, true) ?? [];
        }

        return $data;
    }

    protected function prepareEventsPayload(array $calendars, string $dateYmd, int $itemsPerPage = 10): array
    {
        // Trick theodia into not detecting our fetch of information too easily
        // by asking future events with a time anytime between midnight and 07:59:59
        $hour = random_int(0, 7);
        $minute = random_int(0, 59);
        $second = random_int(0, 59);
        $startDate = $dateYmd . 'T' . sprintf('%02d:%02d:%02d', $hour, $minute, $second) . 'Z';

        $parameters = [[
            'operationName' => 'Events',
            'variables' => [
                'pagination' => [
                    'pageIndex' => 0,
                    'pageSize' => $itemsPerPage,
                    'offset' => null,
                ],
                'filter' => [
                    'groups' => [[
                        'joins' => [
                            'calendar' => [
                                'joins' => [
                                    //'rite' => [],
                                    'place' => [
                                        'type' => 'innerJoin',
                                        'joins' => [
                                            'country' => [
                                                'type' => 'leftJoin',
                                            ],
                                        ],
                                    ],
                                ],
                                'type' => 'innerJoin',
                            ],
                        ],
                        'conditions' => [
                            [
                                'isExcluded' => [
                                    'equal' => [
                                        'value' => false,
                                    ]
                                ],
                            ],
                            [
                                'start' => [
                                    'greaterOrEqual' => [
                                        'value' => $startDate,
                                    ],
                                ],
                            ],
                            [
                                'calendar' => [
                                    'in' => [
                                        'values' => $calendars,
                                    ],
                                ],
                            ],
                        ],
                    ]],
                ],
                'sorting' => [[
                    'field' => 'start',
                    'order' => 'ASC',
                ]],
            ],
            'query' => 'query Events($filter: EventFilter, $sorting: [EventSorting!], $pagination: PaginationInput) {
                  events(filter: $filter, sorting: $sorting, pagination: $pagination) {
                    items {
                      id
                      name
                      isAllDay
                      start
                      end
                      calendar {
                        id
                        language
                        rite {
                          id
                          name
                        }
                        place {
                          id
                          name
                          latitude
                          longitude
                        }
                      }
                    }
                    pageSize
                    pageIndex
                    length
                  }
                }
            '
        ]];

        return $parameters;
    }

    protected function prepareFrontOfficePlacePayload(int $placeId): array
    {
        $parameters = [[
            'operationName' => 'FrontOfficePlace',
            'variables' => [
                'id' => $placeId,
            ],
            'query' => 'query FrontOfficePlace($id: PlaceID!) {
                  place(id: $id) {
                    ...FrontOfficePlaceFields
                    __typename
                  }
                }

                fragment FrontOfficePlaceFields on Place {
                  id
                  name
                  street
                  postcode
                  locality
                  area
                  latitude
                  longitude
                  description
                  seats
                  url
                  patron
                  year
                  country {
                    id
                    code
                    name
                  }
                  type {
                    id
                    name
                  }
                  images {
                    id
                    source
                  }
                }
            '
        ]];

        return $parameters;
    }

    /**
     * @param Site $site
     * @param int $id
     * @return array
     */
    protected function createAndFetchTheodiaPlace(Site $site, int $id): array
    {
        $storage = (int)($site->getConfiguration()['tx_theodia_storage'] ?? 0);
        $freeStorage = (bool)($site->getConfiguration()['tx_theodia_free_storage'] ?? false);

        $tableConnection = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('tx_theodia_place');

        $where = [
            'place_id' => $id,
        ];
        if (!empty($storage) && !$freeStorage) {
            $where['pid'] = $storage;
        }

        if (!$freeStorage) {
            $data = $tableConnection
                ->select(
                    ['*'],
                    'tx_theodia_place',
                    $where
                )
                ->fetchAssociative();

            if (!empty($data)) {
                // Place already exists in database, we do not update it
                return $data;
            }
        } else {
            // We need to fetch all possible places in the TYPO3 install and figure
            // out which one is the right one for the corresponding site
            $places = $tableConnection
                ->select(
                    ['*'],
                    'tx_theodia_place',
                    $where
                )
                ->fetchAllAssociative();
            // We first try to find the place in the default storage of the site
            foreach ($places as $place) {
                if ($place['pid'] === $storage) {
                    return $place;
                }
            }
            $siteFinder = GeneralUtility::makeInstance(SiteFinder::class);
            foreach ($places as $place) {
                $placeSite = $siteFinder->getSiteByPageId($place['pid']);
                if ($placeSite === $site) {
                    return $place;
                }
            }
        }

        $payload = $this->prepareFrontOfficePlacePayload($id);
        $info = $this->callApi($payload, 'place_' . $id, 604800 /* 1 week */);
        // TODO: (possibly) extract image from theodia by fetching $info['_links']['images']['href']

        $place = $info[0]['data']['place'];
        $data = [
            'pid' => $storage,
            'tstamp' => $GLOBALS['EXEC_TIME'],
            'crdate' => $GLOBALS['EXEC_TIME'],
            'place_id' => $id,
            'name' => $place['name'],
            'latitude' => $place['latitude'],
            'longitude' => $place['longitude'],
            'patron' => $place['patron'],
            'description' => $place['description'],
            'year' => (int)$place['year'],
            'seats' => (int)($place['seats'] ?? 0),
            'address' => $place['street'],
            'postal_code' => $place['postcode'],
            'city' => $place['locality'],
            'region' => $place['area'],
            'country' => $place['country']['code'] ?? 'CH',
            'url' => $place['url'] ?? '',
        ];

        $tableConnection->insert('tx_theodia_place', $data);

        return $tableConnection
            ->select(
                ['*'],
                'tx_theodia_place',
                $where
            )
            ->fetchAssociative();
    }
}