<?php

function hms_today()
{
    return new DateTimeImmutable('today');
}

function hms_parse_booking_date($value)
{
    if (empty($value)) {
        return null;
    }

    $date = DateTimeImmutable::createFromFormat('Y-m-d', (string) $value);
    if ($date instanceof DateTimeImmutable) {
        return $date->setTime(0, 0, 0);
    }

    $timestamp = strtotime((string) $value);
    if ($timestamp === false) {
        return null;
    }

    return (new DateTimeImmutable('@' . $timestamp))->setTimezone(new DateTimeZone(date_default_timezone_get()))->setTime(0, 0, 0);
}

function hms_calculate_booking_end_date($stayFrom, $duration)
{
    $startDate = hms_parse_booking_date($stayFrom);
    $durationMonths = (int) $duration;

    if (!$startDate || $durationMonths <= 0) {
        return null;
    }

    return $startDate->modify('+' . ($durationMonths * 30) . ' days');
}

function hms_build_booking_timeline($booking)
{
    if (!$booking) {
        return null;
    }

    $startDate = isset($booking['stayfrom']) ? hms_parse_booking_date($booking['stayfrom']) : null;
    $durationMonths = isset($booking['duration']) ? (int) $booking['duration'] : 0;
    $endDate = hms_calculate_booking_end_date(isset($booking['stayfrom']) ? $booking['stayfrom'] : null, $durationMonths);
    $today = hms_today();
    $remainingDays = $endDate ? max(0, (int) $today->diff($endDate)->format('%r%a')) : 0;
    $startsInDays = $startDate ? max(0, (int) $today->diff($startDate)->format('%r%a')) : 0;
    $isActive = $endDate ? $today < $endDate : false;
    $hasStarted = $startDate ? $today >= $startDate : false;

    $booking['stayfrom'] = $startDate ? $startDate->format('Y-m-d') : (isset($booking['stayfrom']) ? $booking['stayfrom'] : '');
    $booking['duration'] = $durationMonths;
    $booking['end_date'] = $endDate ? $endDate->format('Y-m-d') : '';
    $booking['entitled_days'] = max(0, $durationMonths * 30);
    $booking['remaining_days'] = $remainingDays;
    $booking['starts_in_days'] = $startsInDays;
    $booking['has_started'] = $hasStarted;
    $booking['is_active'] = $isActive;
    
    $booking['renewal_count'] = isset($booking['renewal_count']) ? (int) $booking['renewal_count'] : 0;
    $booking['checkout_date'] = isset($booking['checkout_date']) ? $booking['checkout_date'] : null;

    if (!empty($booking['checkout_date'])) {
        $occupancyStatus = 'checked_out';
        $statusLabel = 'Checked Out';
    } elseif ($remainingDays <= 0 && $hasStarted) {
        $occupancyStatus = 'expired';
        $statusLabel = 'Expired';
    } elseif ($remainingDays <= 30 && $hasStarted) {
        $occupancyStatus = 'expiring_soon';
        $statusLabel = 'Expiring Soon';
    } elseif ($hasStarted) {
        $occupancyStatus = 'active';
        $statusLabel = 'Active';
    } else {
        $occupancyStatus = 'upcoming';
        $statusLabel = 'Upcoming';
    }

    $booking['occupancy_status'] = $occupancyStatus;
    $booking['booking_status_label'] = $statusLabel;

    return $booking;
}

function hms_rooms_column_exists(mysqli $mysqli, $columnName)
{
    static $cache = array();

    if (isset($cache[$columnName])) {
        return $cache[$columnName];
    }

    $dbResult = $mysqli->query("SELECT DATABASE() AS dbname");
    $dbRow = $dbResult ? $dbResult->fetch_assoc() : null;
    $dbName = $dbRow ? $dbRow['dbname'] : '';
    if ($dbResult instanceof mysqli_result) {
        $dbResult->free();
    }

    if ($dbName === '') {
        $cache[$columnName] = false;
        return false;
    }

    $stmt = $mysqli->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'rooms' AND COLUMN_NAME = ?");
    $stmt->bind_param('ss', $dbName, $columnName);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    $cache[$columnName] = ((int) $count) > 0;
    return $cache[$columnName];
}

function hms_get_room_feature_columns(mysqli $mysqli)
{
    $columns = array(
        'attached_bathroom',
        'air_conditioner',
        'wifi',
        'balcony',
        'study_table',
        'description',
        'room_status',
    );

    $available = array();
    foreach ($columns as $column) {
        if (hms_rooms_column_exists($mysqli, $column)) {
            $available[] = $column;
        }
    }

    return $available;
}

function hms_rooms_supports_feature_system(mysqli $mysqli)
{
    $requiredColumns = array(
        'attached_bathroom',
        'air_conditioner',
        'wifi',
        'balcony',
        'study_table',
        'description',
        'room_status',
    );

    foreach ($requiredColumns as $column) {
        if (!hms_rooms_column_exists($mysqli, $column)) {
            return false;
        }
    }

    return true;
}

function hms_apply_room_defaults($room)
{
    if (!$room) {
        return null;
    }

    foreach (hms_get_room_feature_keys() as $key => $label) {
        if (!isset($room[$key])) {
            $room[$key] = 0;
        }
    }

    if (!isset($room['description'])) {
        $room['description'] = '';
    }

    if (!isset($room['room_status']) || $room['room_status'] === '') {
        $room['room_status'] = 'available';
    }

    return $room;
}

function hms_get_room_feature_keys()
{
    return array(
        'attached_bathroom' => 'Attached Bathroom',
        'air_conditioner' => 'Air Conditioner',
        'wifi' => 'Wi-Fi',
        'balcony' => 'Balcony',
        'study_table' => 'Study Table',
    );
}

function hms_room_feature_value($value)
{
    return (int) (!empty($value) ? 1 : 0);
}

function hms_prepare_room_feature_payload($source)
{
    $payload = array();
    foreach (hms_get_room_feature_keys() as $key => $label) {
        $payload[$key] = isset($source[$key]) ? 1 : 0;
    }

    return $payload;
}

function hms_get_room_by_number(mysqli $mysqli, $roomNo)
{
    $roomNo = (int) $roomNo;
    $selectColumns = array('id', 'room_no', 'seater', 'fees');
    $selectColumns = array_merge($selectColumns, hms_get_room_feature_columns($mysqli));
    $sql = 'SELECT ' . implode(', ', $selectColumns) . ' FROM rooms WHERE room_no = ? LIMIT 1';
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('i', $roomNo);
    $stmt->execute();
    $result = $stmt->get_result();
    $room = $result->fetch_assoc();
    $stmt->close();

    return hms_apply_room_defaults($room ?: null);
}

function hms_get_active_booking_by_email(mysqli $mysqli, $email)
{
    $stmt = $mysqli->prepare('SELECT id, roomno, emailid, postingDate, stayfrom, duration, feespm, foodstatus, renewal_count, checkout_date FROM registration WHERE emailid = ? ORDER BY id DESC');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $booking = null;

    while ($row = $result->fetch_assoc()) {
        $row = hms_build_booking_timeline($row);
        if (!empty($row['is_active'])) {
            $booking = $row;
            break;
        }
    }
    $stmt->close();

    return $booking ?: null;
}

function hms_get_latest_booking_by_email(mysqli $mysqli, $email)
{
    $stmt = $mysqli->prepare('SELECT id, roomno, emailid, postingDate, stayfrom, duration, feespm, foodstatus, renewal_count, checkout_date FROM registration WHERE emailid = ? ORDER BY id DESC LIMIT 1');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $booking = $result->fetch_assoc();
    $stmt->close();

    return $booking ? hms_build_booking_timeline($booking) : null;
}

function hms_get_bookings_by_email(mysqli $mysqli, $email)
{
    $stmt = $mysqli->prepare('SELECT * FROM registration WHERE emailid = ? ORDER BY id DESC');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $bookings = array();

    while ($row = $result->fetch_assoc()) {
        $bookings[] = hms_build_booking_timeline($row);
    }
    $stmt->close();

    return $bookings;
}

function hms_get_renewable_room_option(mysqli $mysqli, $email)
{
    $activeBooking = hms_get_active_booking_by_email($mysqli, $email);
    if ($activeBooking && !in_array($activeBooking['occupancy_status'], array('expiring_soon', 'expired'), true)) {
        return null;
    }

    $latestBooking = $activeBooking ? $activeBooking : hms_get_latest_booking_by_email($mysqli, $email);
    if (!$latestBooking || empty($latestBooking['roomno'])) {
        return null;
    }

    $roomAvailability = hms_get_room_availability($mysqli, (int) $latestBooking['roomno']);
    if (!$roomAvailability['exists']) {
        return null;
    }

    $room = $roomAvailability['room'];
    $room['remaining'] = $roomAvailability['remaining'];
    $room['is_full'] = $roomAvailability['is_full'];
    $room['previous_booking'] = $latestBooking;
    $room['renew_allowed'] = $room['room_status'] === 'available';
    $room['renewal_window_open'] = in_array($latestBooking['occupancy_status'], array('expiring_soon', 'expired'), true);

    return $room;
}

function hms_count_room_occupancy(mysqli $mysqli, $roomNo)
{
    $roomNo = (int) $roomNo;
    $stmt = $mysqli->prepare('SELECT id, stayfrom, duration FROM registration WHERE roomno = ?');
    $stmt->bind_param('i', $roomNo);
    $stmt->execute();
    $result = $stmt->get_result();
    $count = 0;

    while ($row = $result->fetch_assoc()) {
        $timeline = hms_build_booking_timeline($row);
        if (!empty($timeline['is_active'])) {
            $count++;
        }
    }
    $stmt->close();

    return (int) $count;
}

function hms_get_room_availability(mysqli $mysqli, $roomNo)
{
    $room = hms_get_room_by_number($mysqli, $roomNo);
    if (!$room) {
        return array(
            'exists' => false,
            'is_full' => false,
            'capacity' => 0,
            'occupied' => 0,
            'remaining' => 0,
            'room' => null,
        );
    }

    $occupied = hms_count_room_occupancy($mysqli, $roomNo);
    $capacity = (int) $room['seater'];
    $remaining = max(0, $capacity - $occupied);

    return array(
        'exists' => true,
        'is_full' => $occupied >= $capacity,
        'capacity' => $capacity,
        'occupied' => $occupied,
        'remaining' => $remaining,
        'room' => $room,
    );
}

function hms_get_rooms_with_occupancy(mysqli $mysqli)
{
    $baseColumns = array('r.id', 'r.room_no', 'r.seater', 'r.fees', 'r.posting_date');
    $groupColumns = $baseColumns;
    foreach (hms_get_room_feature_columns($mysqli) as $column) {
        $baseColumns[] = 'r.' . $column;
        $groupColumns[] = 'r.' . $column;
    }

    $sql = "SELECT " . implode(', ', $baseColumns) . ", reg.id AS registration_id, reg.stayfrom AS booking_stayfrom, reg.duration AS booking_duration
            FROM rooms r
            LEFT JOIN registration reg ON reg.roomno = r.room_no
            ORDER BY r.room_no ASC";
    $result = $mysqli->query($sql);
    $rooms = array();
    $roomMap = array();

    if ($result instanceof mysqli_result) {
        while ($row = $result->fetch_assoc()) {
            $roomNo = (int) $row['room_no'];

            if (!isset($roomMap[$roomNo])) {
                $room = $row;
                unset($room['registration_id'], $room['booking_stayfrom'], $room['booking_duration']);
                $room = hms_apply_room_defaults($room);
                $room['occupied'] = 0;
                $room['seater'] = (int) $room['seater'];
                $room['fees'] = (int) $room['fees'];
                $room['attached_bathroom'] = hms_room_feature_value($room['attached_bathroom']);
                $room['air_conditioner'] = hms_room_feature_value($room['air_conditioner']);
                $room['wifi'] = hms_room_feature_value($room['wifi']);
                $room['balcony'] = hms_room_feature_value($room['balcony']);
                $room['study_table'] = hms_room_feature_value($room['study_table']);
                $room['room_status'] = $room['room_status'] !== '' ? $room['room_status'] : 'available';
                $roomMap[$roomNo] = $room;
            }

            if (!empty($row['registration_id'])) {
                $timeline = hms_build_booking_timeline(array(
                    'stayfrom' => $row['booking_stayfrom'],
                    'duration' => $row['booking_duration'],
                ));
                if (!empty($timeline['is_active'])) {
                    $roomMap[$roomNo]['occupied']++;
                }
            }
        }

        foreach ($roomMap as $room) {
            $room['remaining'] = max(0, $room['seater'] - $room['occupied']);
            $room['is_full'] = $room['occupied'] >= $room['seater'];

            if ($room['room_status'] !== 'available') {
                $room['status_key'] = 'inactive';
                $room['status_label'] = ucfirst($room['room_status']);
            } elseif ($room['occupied'] <= 0) {
                $room['status_key'] = 'empty';
                $room['status_label'] = 'Empty';
            } elseif ($room['occupied'] >= $room['seater']) {
                $room['status_key'] = 'full';
                $room['status_label'] = 'Full';
            } else {
                $room['status_key'] = 'partial';
                $room['status_label'] = 'Partially Occupied';
            }

            $rooms[] = $room;
        }
        $result->free();
    }

    return $rooms;
}

function hms_get_available_rooms(mysqli $mysqli)
{
    $rooms = hms_get_rooms_with_occupancy($mysqli);
    $availableRooms = array();

    foreach ($rooms as $room) {
        if ($room['room_status'] === 'available' && !$room['is_full']) {
            $availableRooms[] = $room;
        }
    }

    return $availableRooms;
}

function hms_get_room_feature_list($room)
{
    $features = array();
    foreach (hms_get_room_feature_keys() as $key => $label) {
        if (!empty($room[$key])) {
            $features[] = $label;
        }
    }

    return $features;
}

function hms_get_room_preferences($source)
{
    $preferences = array(
        'seater' => isset($source['pref_seater']) && $source['pref_seater'] !== '' ? (int) $source['pref_seater'] : 0,
        'min_budget' => isset($source['min_budget']) && $source['min_budget'] !== '' ? (int) $source['min_budget'] : 0,
        'max_budget' => isset($source['max_budget']) && $source['max_budget'] !== '' ? (int) $source['max_budget'] : 0,
    );

    foreach (hms_get_room_feature_keys() as $key => $label) {
        $dbKey = isset($source['pref_' . $key]) ? 'pref_' . $key : $key; // support both _GET schema and DB schema
        $preferences[$key] = !empty($source[$dbKey]) ? 1 : 0;
    }

    return $preferences;
}

function hms_get_user_room_preferences(mysqli $mysqli, $email)
{
    $stmt = $mysqli->prepare('SELECT pref_seater, pref_attached_bathroom, pref_air_conditioner, pref_wifi, pref_balcony, pref_study_table FROM userregistration WHERE email = ? LIMIT 1');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $profile = $result->fetch_assoc();
    $stmt->close();

    if (!$profile) {
        return null;
    }

    return hms_get_room_preferences($profile);
}

function hms_room_matches_preferences($room, $preferences)
{
    foreach (hms_get_room_feature_keys() as $key => $label) {
        if (!empty($preferences[$key]) && empty($room[$key])) {
            return false;
        }
    }

    if (!empty($preferences['seater']) && (int) $room['seater'] !== (int) $preferences['seater']) {
        return false;
    }

    if (!empty($preferences['min_budget']) && (int) $room['fees'] < (int) $preferences['min_budget']) {
        return false;
    }

    if (!empty($preferences['max_budget']) && (int) $room['fees'] > (int) $preferences['max_budget']) {
        return false;
    }

    return true;
}

function hms_calculate_room_match_score($room, $preferences)
{
    $score = 0;

    foreach (hms_get_room_feature_keys() as $key => $label) {
        if (!empty($preferences[$key]) && !empty($room[$key])) {
            $score += 2;
        }
    }

    if (!empty($preferences['seater']) && (int) $room['seater'] === (int) $preferences['seater']) {
        $score += 2;
    }

    if (!empty($preferences['min_budget']) && (int) $room['fees'] >= (int) $preferences['min_budget']) {
        $score += 1;
    }

    if (!empty($preferences['max_budget']) && (int) $room['fees'] <= (int) $preferences['max_budget']) {
        $score += 1;
    }

    return $score;
}

function hms_has_room_preferences($preferences)
{
    if (!empty($preferences['seater']) || !empty($preferences['min_budget']) || !empty($preferences['max_budget'])) {
        return true;
    }

    foreach (hms_get_room_feature_keys() as $key => $label) {
        if (!empty($preferences[$key])) {
            return true;
        }
    }

    return false;
}

function hms_get_room_suggestions(mysqli $mysqli, $preferences)
{
    $rooms = hms_get_available_rooms($mysqli);
    $exactMatches = array();
    $closeMatches = array();
    $otherRooms = array();
    $hasPreferences = hms_has_room_preferences($preferences);

    foreach ($rooms as $room) {
        $room['feature_list'] = hms_get_room_feature_list($room);
        $room['match_score'] = hms_calculate_room_match_score($room, $preferences);

        if (!$hasPreferences) {
            $otherRooms[] = $room;
            continue;
        }

        if (hms_room_matches_preferences($room, $preferences)) {
            $exactMatches[] = $room;
        } elseif ($room['match_score'] > 0) {
            $closeMatches[] = $room;
        } else {
            $otherRooms[] = $room;
        }
    }

    usort($exactMatches, 'hms_sort_rooms_by_match');
    usort($closeMatches, 'hms_sort_rooms_by_match');
    usort($otherRooms, 'hms_sort_rooms_by_match');

    $bestMatches = array();
    if ($hasPreferences) {
        $bestMatches = array_slice($exactMatches, 0, 3);
        $exactMatches = array_slice($exactMatches, 3);
    }

    return array(
        'best_matches' => $bestMatches,
        'exact_matches' => $exactMatches,
        'close_matches' => $closeMatches,
        'other_available' => $otherRooms,
    );
}

function hms_sort_rooms_by_match($left, $right)
{
    if ($left['match_score'] === $right['match_score']) {
        if ($left['fees'] === $right['fees']) {
            return $left['room_no'] <=> $right['room_no'];
        }

        return $left['fees'] <=> $right['fees'];
    }

    return $right['match_score'] <=> $left['match_score'];
}

function hms_calculate_pending_booking_amounts(array $pendingBooking)
{
    $monthlyFee = isset($pendingBooking['feespm']) ? (int) $pendingBooking['feespm'] : 0;
    $duration = isset($pendingBooking['duration']) ? (int) $pendingBooking['duration'] : 0;
    $foodStatus = isset($pendingBooking['foodstatus']) ? (int) $pendingBooking['foodstatus'] : 0;
    $extraFoodCharge = $foodStatus === 1 ? 2000 : 0;
    $totalRs = max(0, ($monthlyFee + $extraFoodCharge) * $duration);

    return array(
        'monthly_fee' => $monthlyFee,
        'duration' => $duration,
        'food_charge' => $extraFoodCharge,
        'total_rs' => $totalRs,
        'total_paisa' => $totalRs * 100,
    );
}

function hms_pending_booking_student_name(array $pendingBooking)
{
    return trim(
        (isset($pendingBooking['fname']) ? $pendingBooking['fname'] : '') . ' ' .
        (isset($pendingBooking['mname']) ? $pendingBooking['mname'] : '') . ' ' .
        (isset($pendingBooking['lname']) ? $pendingBooking['lname'] : '')
    );
}

function hms_khalti_is_configured()
{
    return defined('KHALTI_SECRET_KEY') && trim((string) KHALTI_SECRET_KEY) !== '';
}

function hms_khalti_base_url()
{
    $environment = defined('KHALTI_ENVIRONMENT') ? strtolower(trim((string) KHALTI_ENVIRONMENT)) : 'sandbox';

    if ($environment === 'production') {
        return defined('KHALTI_PRODUCTION_BASE_URL')
            ? rtrim((string) KHALTI_PRODUCTION_BASE_URL, '/') . '/'
            : 'https://khalti.com/api/v2/';
    }

    return defined('KHALTI_SANDBOX_BASE_URL')
        ? rtrim((string) KHALTI_SANDBOX_BASE_URL, '/') . '/'
        : 'https://dev.khalti.com/api/v2/';
}

function hms_khalti_environment()
{
    return defined('KHALTI_ENVIRONMENT') ? strtolower(trim((string) KHALTI_ENVIRONMENT)) : 'sandbox';
}

function hms_khalti_checkout_url($paymentUrl)
{
    $paymentUrl = trim((string) $paymentUrl);
    if ($paymentUrl === '') {
        return '';
    }

    $parts = parse_url($paymentUrl);
    if ($parts === false || empty($parts['host'])) {
        return $paymentUrl;
    }

    $host = strtolower((string) $parts['host']);
    if (hms_khalti_environment() !== 'production' && $host === 'pay.khalti.com') {
        $parts['host'] = 'test-pay.khalti.com';
    }

    $scheme = isset($parts['scheme']) ? $parts['scheme'] . '://' : 'https://';
    $user = isset($parts['user']) ? $parts['user'] : '';
    $pass = isset($parts['pass']) ? ':' . $parts['pass'] : '';
    $auth = $user !== '' ? $user . $pass . '@' : '';
    $port = isset($parts['port']) ? ':' . $parts['port'] : '';
    $path = isset($parts['path']) ? $parts['path'] : '';
    $query = isset($parts['query']) ? '?' . $parts['query'] : '';
    $fragment = isset($parts['fragment']) ? '#' . $parts['fragment'] : '';

    return $scheme . $auth . $parts['host'] . $port . $path . $query . $fragment;
}

function hms_app_base_url()
{
    $isHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    $scheme = $isHttps ? 'https' : 'http';
    $host = isset($_SERVER['HTTP_HOST']) && trim((string) $_SERVER['HTTP_HOST']) !== ''
        ? trim((string) $_SERVER['HTTP_HOST'])
        : 'localhost';
    $scriptName = isset($_SERVER['SCRIPT_NAME']) ? str_replace('\\', '/', (string) $_SERVER['SCRIPT_NAME']) : '/hostel/payment.php';
    $basePath = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');

    if ($basePath === '' || $basePath === '.') {
        $basePath = '';
    }

    return $scheme . '://' . $host . $basePath;
}

function hms_khalti_api_request($endpoint, array $payload)
{
    if (!hms_khalti_is_configured()) {
        return array(
            'success' => false,
            'status_code' => 0,
            'data' => null,
            'error' => 'Khalti secret key is not configured yet.',
        );
    }

    $url = hms_khalti_base_url() . ltrim((string) $endpoint, '/');
    $headers = array(
        'Authorization: Key ' . trim((string) KHALTI_SECRET_KEY),
        'Content-Type: application/json',
        'Accept: application/json',
    );

    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_TIMEOUT => 30,
    ));

    $responseBody = curl_exec($curl);
    $curlError = curl_error($curl);
    $statusCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    if ($responseBody === false) {
        return array(
            'success' => false,
            'status_code' => $statusCode,
            'data' => null,
            'error' => $curlError !== '' ? $curlError : 'Khalti request failed.',
        );
    }

    $decoded = json_decode($responseBody, true);

    return array(
        'success' => $statusCode >= 200 && $statusCode < 300,
        'status_code' => $statusCode,
        'data' => is_array($decoded) ? $decoded : array(),
        'error' => $statusCode >= 200 && $statusCode < 300 ? '' : $responseBody,
    );
}

function hms_khalti_initiate_payment(array $pendingBooking)
{
    $amounts = hms_calculate_pending_booking_amounts($pendingBooking);
    $roomNo = isset($pendingBooking['roomno']) ? (int) $pendingBooking['roomno'] : 0;
    $regNo = isset($pendingBooking['regno']) ? trim((string) $pendingBooking['regno']) : 'booking';
    $purchaseOrderId = 'HOSTEL-' . $regNo . '-' . $roomNo . '-' . time();
    $studentName = hms_pending_booking_student_name($pendingBooking);
    $studentEmail = isset($pendingBooking['emailid']) ? trim((string) $pendingBooking['emailid']) : '';
    $studentPhone = isset($pendingBooking['contactno']) ? preg_replace('/\D+/', '', (string) $pendingBooking['contactno']) : '';

    $payload = array(
        'return_url' => hms_app_base_url() . '/khalti_callback.php',
        'website_url' => hms_app_base_url() . '/',
        'amount' => $amounts['total_paisa'],
        'purchase_order_id' => $purchaseOrderId,
        'purchase_order_name' => 'Hostel booking for room ' . $roomNo,
        'customer_info' => array(
            'name' => $studentName !== '' ? $studentName : 'Student',
            'email' => $studentEmail,
            'phone' => $studentPhone,
        ),
    );

    $response = hms_khalti_api_request('epayment/initiate/', $payload);
    if ($response['success'] && !empty($response['data']['pidx']) && !empty($response['data']['payment_url'])) {
        $response['data']['payment_url'] = hms_khalti_checkout_url($response['data']['payment_url']);
        $response['purchase_order_id'] = $purchaseOrderId;
    }

    return $response;
}

function hms_khalti_lookup_payment($pidx)
{
    return hms_khalti_api_request('epayment/lookup/', array(
        'pidx' => (string) $pidx,
    ));
}

function hms_finalize_pending_booking(mysqli $mysqli, array $pendingBooking, $paymentMethod, $paymentStatus, $transactionId)
{
    $emailId = isset($pendingBooking['emailid']) ? trim((string) $pendingBooking['emailid']) : '';
    $roomNo = isset($pendingBooking['roomno']) ? (int) $pendingBooking['roomno'] : 0;
    $transactionId = trim((string) $transactionId);
    $isRenewal = !empty($pendingBooking['is_renewal']);
    $renewalSourceBookingId = isset($pendingBooking['renewal_source_booking_id']) ? (int) $pendingBooking['renewal_source_booking_id'] : 0;
    $pendingStayFrom = isset($pendingBooking['stayfrom']) ? hms_parse_booking_date($pendingBooking['stayfrom']) : null;

    if ($emailId === '' || $roomNo <= 0) {
        return array(
            'success' => false,
            'message' => 'Pending booking data is incomplete.',
        );
    }

    if ($transactionId !== '') {
        $checkTxnStmt = $mysqli->prepare('SELECT id FROM registration WHERE transaction_id = ? LIMIT 1');
        $checkTxnStmt->bind_param('s', $transactionId);
        $checkTxnStmt->execute();
        $checkTxnStmt->store_result();
        $alreadyExists = $checkTxnStmt->num_rows > 0;
        $checkTxnStmt->close();

        if ($alreadyExists) {
            return array(
                'success' => true,
                'message' => 'Payment was already applied earlier.',
                'already_processed' => true,
            );
        }
    }

    $activeBooking = hms_get_active_booking_by_email($mysqli, $emailId);
    if ($activeBooking) {
        $allowedRenewal = false;
        if (
            $isRenewal &&
            $renewalSourceBookingId > 0 &&
            (int) $activeBooking['id'] === $renewalSourceBookingId &&
            in_array($activeBooking['occupancy_status'], array('expiring_soon', 'expired'), true) &&
            $pendingStayFrom instanceof DateTimeImmutable &&
            !empty($activeBooking['end_date']) &&
            $pendingStayFrom >= hms_parse_booking_date($activeBooking['end_date'])
        ) {
            $allowedRenewal = true;
        }

        if (!$allowedRenewal) {
            return array(
                'success' => false,
                'message' => 'This user already has an active booking.',
            );
        }
    }

    $roomAvailability = hms_get_room_availability($mysqli, $roomNo);
    if (!$roomAvailability['exists']) {
        return array(
            'success' => false,
            'message' => 'Selected room no longer exists.',
        );
    }

    if ($roomAvailability['room']['room_status'] !== 'available') {
        return array(
            'success' => false,
            'message' => 'Selected room is not currently available.',
        );
    }

    if ($roomAvailability['is_full'] && !$isRenewal) {
        return array(
            'success' => false,
            'message' => 'Selected room is already full.',
        );
    }

    $latestBooking = hms_get_latest_booking_by_email($mysqli, $emailId);
    $existingBookingId = $latestBooking ? (int) $latestBooking['id'] : 0;
    $isExistingRenewal = $isRenewal && $renewalSourceBookingId > 0 && $existingBookingId === $renewalSourceBookingId;

    if ($existingBookingId > 0) {
        $query = 'UPDATE registration SET
            roomno = ?, seater = ?, feespm = ?, foodstatus = ?, stayfrom = ?, duration = ?, course = ?, regno = ?, firstName = ?, middleName = ?, lastName = ?, gender = ?, contactno = ?, emailid = ?, egycontactno = ?, guardianName = ?, guardianRelation = ?, guardianContactno = ?, corresAddress = ?, corresCIty = ?, corresState = ?, corresPincode = ?, pmntAddress = ?, pmntCity = ?, pmnatetState = ?, pmntPincode = ?, payment_method = ?, payment_status = ?, transaction_id = ?, checkout_date = NULL, renewal_count = ?
            WHERE id = ? LIMIT 1';

        $stmt = $mysqli->prepare($query);
        if (!$stmt) {
            return array(
                'success' => false,
                'message' => 'Unable to prepare booking update.',
            );
        }

        $renewalCount = isset($latestBooking['renewal_count']) ? (int) $latestBooking['renewal_count'] : 0;
        if ($isExistingRenewal) {
            $renewalCount++;
        }

        $types = str_repeat('s', 29) . 'ii';
        $stmt->bind_param(
            $types,
            $pendingBooking['roomno'],
            $pendingBooking['seater'],
            $pendingBooking['feespm'],
            $pendingBooking['foodstatus'],
            $pendingBooking['stayfrom'],
            $pendingBooking['duration'],
            $pendingBooking['course'],
            $pendingBooking['regno'],
            $pendingBooking['fname'],
            $pendingBooking['mname'],
            $pendingBooking['lname'],
            $pendingBooking['gender'],
            $pendingBooking['contactno'],
            $pendingBooking['emailid'],
            $pendingBooking['emcntno'],
            $pendingBooking['gurname'],
            $pendingBooking['gurrelation'],
            $pendingBooking['gurcntno'],
            $pendingBooking['caddress'],
            $pendingBooking['ccity'],
            $pendingBooking['cstate'],
            $pendingBooking['cpincode'],
            $pendingBooking['paddress'],
            $pendingBooking['pcity'],
            $pendingBooking['pstate'],
            $pendingBooking['ppincode'],
            $paymentMethod,
            $paymentStatus,
            $transactionId,
            $renewalCount,
            $existingBookingId
        );
    } else {
        $query = 'INSERT INTO registration(
            roomno,seater,feespm,foodstatus,stayfrom,duration,course,regno,firstName,middleName,lastName,gender,contactno,emailid,egycontactno,guardianName,guardianRelation,guardianContactno,corresAddress,corresCIty,corresState,corresPincode,pmntAddress,pmntCity,pmnatetState,pmntPincode,payment_method,payment_status,transaction_id
        ) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)';

        $stmt = $mysqli->prepare($query);
        if (!$stmt) {
            return array(
                'success' => false,
                'message' => 'Unable to prepare booking allocation.',
            );
        }

        $types = str_repeat('s', 29);
        $stmt->bind_param(
            $types,
            $pendingBooking['roomno'],
            $pendingBooking['seater'],
            $pendingBooking['feespm'],
            $pendingBooking['foodstatus'],
            $pendingBooking['stayfrom'],
            $pendingBooking['duration'],
            $pendingBooking['course'],
            $pendingBooking['regno'],
            $pendingBooking['fname'],
            $pendingBooking['mname'],
            $pendingBooking['lname'],
            $pendingBooking['gender'],
            $pendingBooking['contactno'],
            $pendingBooking['emailid'],
            $pendingBooking['emcntno'],
            $pendingBooking['gurname'],
            $pendingBooking['gurrelation'],
            $pendingBooking['gurcntno'],
            $pendingBooking['caddress'],
            $pendingBooking['ccity'],
            $pendingBooking['cstate'],
            $pendingBooking['cpincode'],
            $pendingBooking['paddress'],
            $pendingBooking['pcity'],
            $pendingBooking['pstate'],
            $pendingBooking['ppincode'],
            $paymentMethod,
            $paymentStatus,
            $transactionId
        );
    }

    try {
        $executed = $stmt->execute();
    } catch (mysqli_sql_exception $exception) {
        $stmt->close();
        return array(
            'success' => false,
            'message' => 'Failed to save the booking. ' . $exception->getMessage(),
        );
    }

    $stmt->close();

    if (!$executed) {
        return array(
            'success' => false,
            'message' => 'Failed to allocate the room after payment.',
        );
    }

    return array(
        'success' => true,
        'message' => 'Payment confirmed and room allocated successfully.',
        'room_availability' => hms_get_room_availability($mysqli, $roomNo),
    );
}

function hms_notifications_table_exists(mysqli $mysqli)
{
    static $checked = null;

    if ($checked !== null) {
        return $checked;
    }

    $result = $mysqli->query("SHOW TABLES LIKE 'notifications'");
    $checked = $result instanceof mysqli_result && $result->num_rows > 0;

    if ($result instanceof mysqli_result) {
        $result->free();
    }

    return $checked;
}

function hms_create_notification_if_missing(mysqli $mysqli, $receiverEmail, $title, $message)
{
    if (!hms_notifications_table_exists($mysqli)) {
        return false;
    }

    $checkStmt = $mysqli->prepare('SELECT id FROM notifications WHERE receiver_email = ? AND title = ? AND message = ? LIMIT 1');
    if (!$checkStmt) {
        return false;
    }

    $checkStmt->bind_param('sss', $receiverEmail, $title, $message);
    $checkStmt->execute();
    $checkStmt->store_result();
    $exists = $checkStmt->num_rows > 0;
    $checkStmt->close();

    if ($exists) {
        return false;
    }

    $insertStmt = $mysqli->prepare('INSERT INTO notifications (receiver_email, title, message) VALUES (?, ?, ?)');
    if (!$insertStmt) {
        return false;
    }

    $insertStmt->bind_param('sss', $receiverEmail, $title, $message);
    $created = $insertStmt->execute();
    $insertStmt->close();

    return (bool) $created;
}

function hms_trigger_expiry_notification_for_booking(mysqli $mysqli, array $booking)
{
    $receiverEmail = isset($booking['emailid']) ? trim((string) $booking['emailid']) : '';
    if ($receiverEmail === '') {
        return false;
    }

    $remainingDays = isset($booking['remaining_days']) ? (int) $booking['remaining_days'] : -1;
    $hasStarted = !empty($booking['has_started']);
    $isActive = !empty($booking['is_active']);
    $checkoutDate = isset($booking['checkout_date']) ? trim((string) $booking['checkout_date']) : '';

    if (!$hasStarted || !$isActive || $checkoutDate !== '' || $remainingDays < 0 || $remainingDays > 2) {
        return false;
    }

    $roomNo = isset($booking['roomno']) ? (int) $booking['roomno'] : 0;
    $endDate = isset($booking['end_date']) ? trim((string) $booking['end_date']) : '';
    $title = 'Room Expiry Reminder';
    $message = 'Your booking for Room ' . $roomNo . ' will expire in ' . $remainingDays . ' day(s) on ' . $endDate . '. Please renew or make a new booking before the expiry date.';

    $created = hms_create_notification_if_missing($mysqli, $receiverEmail, $title, $message);

    $adminTitle = 'Student Room Expiry Alert';
    $studentName = trim(
        (isset($booking['firstName']) ? $booking['firstName'] : '') . ' ' .
        (isset($booking['middleName']) ? $booking['middleName'] : '') . ' ' .
        (isset($booking['lastName']) ? $booking['lastName'] : '')
    );
    if ($studentName === '') {
        $studentName = $receiverEmail;
    }
    $adminMessage = $studentName . ' (' . $receiverEmail . ') has a booking for Room ' . $roomNo . ' that will expire in ' . $remainingDays . ' day(s) on ' . $endDate . '.';

    foreach (hms_get_admin_notification_emails($mysqli) as $adminEmail) {
        $created = hms_create_notification_if_missing($mysqli, $adminEmail, $adminTitle, $adminMessage) || $created;
    }

    return $created;
}

function hms_run_automatic_expiry_notifications(mysqli $mysqli, $email)
{
    if (trim((string) $email) === '') {
        return;
    }

    foreach (hms_get_bookings_by_email($mysqli, $email) as $booking) {
        hms_trigger_expiry_notification_for_booking($mysqli, $booking);
    }
}

function hms_get_admin_notification_emails(mysqli $mysqli)
{
    $emails = array();

    $userResult = $mysqli->query("SELECT email FROM users WHERE role = 'admin'");
    if ($userResult instanceof mysqli_result) {
        while ($row = $userResult->fetch_assoc()) {
            $email = trim((string) $row['email']);
            if ($email !== '') {
                $emails[$email] = $email;
            }
        }
        $userResult->free();
    }

    if (!$emails) {
        $adminResult = $mysqli->query("SELECT email FROM admin");
        if ($adminResult instanceof mysqli_result) {
            while ($row = $adminResult->fetch_assoc()) {
                $email = trim((string) $row['email']);
                if ($email !== '') {
                    $emails[$email] = $email;
                }
            }
            $adminResult->free();
        }
    }

    return array_values($emails);
}
