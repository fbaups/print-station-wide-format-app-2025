<?php

namespace App\Utility\Feedback;

use Cake\Core\Configure;
use Cake\ORM\Query;
use DebugKit\DebugSql;

class DebugSqlCapture
{

    /**
     * Debug an SQL Query
     *
     * @param Query $query
     * @param bool $showHtml
     * @return array|false|mixed|string
     */
    public static function captureDump(Query $query, $showHtml = false)
    {
        $showValues = true;
        $stackDepth = 0;

        $originalDebugValue = Configure::read('debug');
        Configure::write('debug', true);

        ob_start();
        DebugSql::sql($query, $showValues, $showHtml, $stackDepth);
        $data = ob_get_contents();
        ob_end_clean();

        $data = str_replace('########## DEBUG ##########', '', $data);
        $data = str_replace('###########################', '', $data);
        $data = explode("\n", $data);
        if (isset($data[0])) {
            if (str_starts_with($data[0], '\\src\\Utility')) {
                unset($data[0]);
            }
        }
        $data = implode("\n", $data);
        $data = trim($data);


        Configure::write('debug', $originalDebugValue);

        return $data;
    }


}
