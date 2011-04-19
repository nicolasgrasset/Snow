<?php
/**
 * Snow MySQLi Result
 * 
 * MySQLi result object wrapper to abstract sql databases access 
 * and be able to rely on isnow_db interface
 * 
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2011 RIVER (www.river.se)
 * @package       snow
 * @since         Snow v 0.9
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 * 
 */

class snow_mysqli_result extends mysqli_result
	implements isnow_db_result
{
    public function fetch()
    {
        return $this->fetch_assoc();
    }

    public function fetchAll()
    {
        $rows = array();
        while($row = $this->fetch())
        {
            $rows[] = $row;
        }
        return $rows;
    }
};
