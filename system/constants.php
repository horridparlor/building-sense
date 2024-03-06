<?php
const STATE_ONLINE = 1;
const STATE_OFFLINE = 2;
const STATE_ERROR = 3;
const ABSOLUTE_MAX_ITEMS_FETCHED = 9999;

class Utility {
    public static function limitMaxItems(?int $value): int {
        return is_null($value) ?
            ABSOLUTE_MAX_ITEMS_FETCHED
            : min($value, ABSOLUTE_MAX_ITEMS_FETCHED);
    }
}
