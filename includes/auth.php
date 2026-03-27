<?php
function hms_password_hash($plainPassword)
{
    return password_hash($plainPassword, PASSWORD_DEFAULT);
}

function hms_password_verify($plainPassword, $storedPassword)
{
    $storedPassword = (string) $storedPassword;
    if ($storedPassword === '') {
        return false;
    }

    $info = password_get_info($storedPassword);
    if (!empty($info['algo'])) {
        return password_verify($plainPassword, $storedPassword);
    }

    return hash_equals($storedPassword, (string) $plainPassword);
}

function hms_password_needs_upgrade($storedPassword)
{
    $storedPassword = (string) $storedPassword;
    if ($storedPassword === '') {
        return true;
    }

    $info = password_get_info($storedPassword);
    if (empty($info['algo'])) {
        return true;
    }

    return password_needs_rehash($storedPassword, PASSWORD_DEFAULT);
}

