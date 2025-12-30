<?php
/**
 * Bin Collection API
 * Fetches and parses South Oxfordshire District Council iCal feed
 * to determine next bin collection date and which bins are due
 */
define('KIOSK_APP', true);
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

date_default_timezone_set(TIMEZONE);

/**
 * Gets the configured calendar ID based on calendar type and collection day
 * @return string Calendar ID or empty string if not configured
 */
function getCalendarId() {
    $constName = 'BIN_CALENDAR_ID_' . strtoupper(BIN_CALENDAR_TYPE) . '_' . strtoupper(BIN_COLLECTION_DAY);
    return defined($constName) ? constant($constName) : '';
}

/**
 * Fetches iCal data from Google Calendar with caching
 * @return string iCal data or empty string on failure
 */
function fetchIcalData() {
    $calendarId = getCalendarId();

    if (empty($calendarId)) {
        return '';
    }

    // Ensure cache directory exists
    if (!is_dir(CACHE_DIR)) {
        mkdir(CACHE_DIR, 0755, true);
    }

    $cacheFile = CACHE_DIR . 'bins_ical_' . md5($calendarId) . '.ics';

    // Check cache
    if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < BIN_CACHE_DURATION) {
        return file_get_contents($cacheFile);
    }

    // Construct iCal URL
    $icalUrl = 'https://calendar.google.com/calendar/ical/' . urlencode($calendarId) . '/public/basic.ics';

    $context = stream_context_create([
        'http' => [
            'timeout' => 15,
            'user_agent' => 'Kiosk Display/1.0'
        ]
    ]);

    $icalData = @file_get_contents($icalUrl, false, $context);

    if ($icalData !== false) {
        file_put_contents($cacheFile, $icalData);
        return $icalData;
    }

    // Return stale cache if available
    if (file_exists($cacheFile)) {
        return file_get_contents($cacheFile);
    }

    return '';
}

/**
 * Parses iCal data and extracts events with dates
 * @param string $icalData Raw iCal content
 * @return array Array of events with 'date' (Ymd) and 'summary' keys
 */
function parseIcal($icalData) {
    $events = [];
    $lines = explode("\n", str_replace("\r\n", "\n", $icalData));

    $currentEvent = null;
    $inEvent = false;

    foreach ($lines as $line) {
        $line = trim($line);

        // Handle line continuations (lines starting with space)
        if (!empty($line) && $line[0] === ' ' && $currentEvent !== null && isset($currentEvent['summary'])) {
            $currentEvent['summary'] .= trim($line);
            continue;
        }

        if ($line === 'BEGIN:VEVENT') {
            $currentEvent = ['date' => null, 'summary' => '', 'isAllDay' => false];
            $inEvent = true;
        } elseif ($line === 'END:VEVENT' && $inEvent) {
            // Only include all-day events (actual collection days, not reminder events)
            if ($currentEvent['date'] !== null && !empty($currentEvent['summary']) && $currentEvent['isAllDay']) {
                $events[] = $currentEvent;
            }
            $currentEvent = null;
            $inEvent = false;
        } elseif ($inEvent && $currentEvent !== null) {
            // Only capture all-day events (VALUE=DATE format, not timed reminder events)
            if (strpos($line, 'DTSTART;VALUE=DATE:') === 0) {
                $currentEvent['date'] = substr($line, 19, 8);
                $currentEvent['isAllDay'] = true;
            }
            // Parse SUMMARY
            elseif (strpos($line, 'SUMMARY:') === 0) {
                $currentEvent['summary'] = substr($line, 8);
            }
        }
    }

    return $events;
}

/**
 * Determines which bins are due based on event summary text
 * @param string $summary Event summary text
 * @return array Associative array of bin types and whether they're due
 */
function parseBinsFromSummary($summary) {
    $summary = strtolower($summary);

    $bins = [
        'green' => false,
        'grey' => false,
        'brown' => false
    ];

    // Green bin (recycling)
    if (strpos($summary, 'recycling') !== false || strpos($summary, 'green') !== false) {
        $bins['green'] = true;
    }

    // Grey bin (general waste/rubbish)
    if (strpos($summary, 'grey') !== false ||
        strpos($summary, 'gray') !== false ||
        strpos($summary, 'rubbish') !== false ||
        strpos($summary, 'refuse') !== false) {
        $bins['grey'] = true;
    }

    // Brown bin (garden and food waste)
    if (strpos($summary, 'brown') !== false ||
        strpos($summary, 'garden') !== false ||
        strpos($summary, 'food') !== false ||
        strpos($summary, 'organic') !== false) {
        $bins['brown'] = true;
    }

    return $bins;
}

/**
 * Gets the next bin collection information
 * @return array Collection data including date, bins, and status
 */
function getBinCollection() {
    $today = date('Ymd');
    $todayDate = new DateTime();

    $icalData = fetchIcalData();

    if (empty($icalData)) {
        return [
            'error' => 'Unable to fetch bin collection calendar. Check config.php settings.',
            'date' => date('D j M'),
            'bins' => ['green' => false, 'grey' => false, 'brown' => false],
            'nextCollection' => null,
            'isToday' => false,
            'daysUntil' => null
        ];
    }

    $events = parseIcal($icalData);

    // Find the next upcoming collection (today or future)
    $nextDate = null;
    $nextBins = ['green' => false, 'grey' => false, 'brown' => false];

    // Sort events by date
    usort($events, function($a, $b) {
        return strcmp($a['date'], $b['date']);
    });

    foreach ($events as $event) {
        // Only consider events from today onwards
        if ($event['date'] >= $today) {
            if ($nextDate === null) {
                $nextDate = $event['date'];
            }

            // Collect all bins for the next collection date
            if ($event['date'] === $nextDate) {
                $eventBins = parseBinsFromSummary($event['summary']);
                foreach ($eventBins as $bin => $isDue) {
                    if ($isDue) {
                        $nextBins[$bin] = true;
                    }
                }
            } else {
                // We've moved past the next collection date
                break;
            }
        }
    }

    // Calculate days until collection
    $daysUntil = null;
    $isToday = false;

    if ($nextDate !== null) {
        $collectionDate = DateTime::createFromFormat('Ymd', $nextDate);
        $diff = $todayDate->diff($collectionDate);
        $daysUntil = (int)$diff->format('%r%a');
        $isToday = ($daysUntil === 0);
    }

    // Format the date for display
    $formattedDate = $nextDate
        ? date('D j M', strtotime($nextDate))
        : date('D j M');

    return [
        'date' => $formattedDate,
        'bins' => $nextBins,
        'nextCollection' => $nextDate,
        'isToday' => $isToday,
        'daysUntil' => $daysUntil
    ];
}

echo json_encode(getBinCollection());
