<?php
namespace App\Helpers;

class Equipment {

    /**
     * Static container for the Equipment
     * types.
     * @var Array
     */
    protected static $types = [];

    /**
     * Returns a human readable String that
     * describes the equipment type
     * @param Integer $id
     * @return String
     */
    public static function get($id) {
        // Set the predefined list that can be mapped
        self::$types = [
            0 => 'Légkondícionáló',
            1 => 'WC',
            2 => 'Napelem',
            3 => 'Kert',
        ];

        return (isset(self::$types[$id])) ? self::$types[$id] : ' - ';
    }

}