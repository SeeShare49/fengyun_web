<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/8/21
 * Time: 11:15
 */


class Util
{
    /**
     * @param $database
     * @param $table
     * @param $server_id
     * @param string $field
     * @param string $where
     * @param string $type
     * @param string $order
     * @param string $limit
     *
     * @return array
     */
    public static function get_single_data_byMysql($database, $table, $server_id, $field = "*", $where = '', $type = 'getList', $order = '', $limit = '')
    {
        $server_info = (new ServerModel())->handle('getOne', array("where" => "server_id={$server_id}"), 'server');
        if (!$server_info) return;

        if ($database == 'log') {
            $base = $server_info['log_table'];
        } elseif ($database == 'actor') {
            $base = $server_info['actor_table'];
        }

        $sql = "SELECT {$field} FROM `{$table}`";
        $where && $sql .= " WHERE $where";
        $order && $sql .= " ORDER BY $order";
        $limit && $sql .= " LIMIT $limit";

        $mysql = self::getMysqlCon($server_info['mysql_host'], $server_info['mysql_user'], $server_info['mysql_passwd'], $base, $server_info['mysql_port']);
        $query = $mysql->query($sql);
        if (!$query) return array();

        switch ($type) {
            case 'getOne':
                $res = $query->fetch_assoc();
                break;
            case 'getList':
                while ($row = $query->fetch_assoc()) {
                    $res[] = $row;
                }
                break;
            case 'dataTable':
                while ($row = $query->fetch_assoc()) {
                    $res[] = array_values($row);
                }

                $sql = "SELECT COUNT(*) AS count_data FROM  `{$table}`";
                $where && $sql = " WHERE $where";

                $query = $mysql->query($sql);
                $row = $query->fetch_assoc();
                $iTotalRecords = $row['count_data'];

                $sql = "SELECT COUNT(*) AS count_data FROM  `{$table}`";
                $query = $mysql->query($sql);
                $row = $query->fetch_assoc();
                $iTotalDisplayRecords = $row['count_data'];

                /** @var TYPE_NAME $res */
                $res = array('aaData' => $res, 'iTotalRecords' => $iTotalRecords, 'iTotalDisplayRecords' => $iTotalDisplayRecords);
                break;
        }
        $mysql->close();
        return $res;
    }


    public static function get_single_data_mysql($database, $table, $field = '*', $where = '', $type = 'getList', $order = '', $limit = '')
    {
//        $server_info = (new ServerModel())->handle();
    }

}