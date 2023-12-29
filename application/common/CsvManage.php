<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/9/25
 * Time: 15:42
 */

namespace app\common;


use think\facade\Cache;
use think\facade\Log;

class CsvManage extends \think\Controller
{
    /**
     * 导出csv文件
     * @param $list
     * @param $title
     */
    public function put_csv($list, $title)
    {
        $file_name = "exam" . time() . ".csv";
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename=' . $file_name);
        header('Cache-Control: max-age=0');
        $file = fopen('php://output', "a");
        $limit = 1000;
        $calc = 0;
        foreach ($title as $v) {
            $tit[] = iconv('UTF-8', 'GB2312//IGNORE', $v);
        }
        fputcsv($file, $tit);
        foreach ($list as $v) {
            $calc++;
            if ($limit == $calc) {
                ob_flush();
                flush();
                $calc = 0;
            }
            foreach ($v as $t) {
                /** @var TYPE_NAME $tarr */
                $tarr[] = iconv('UTF-8', 'GB2312//IGNORE', $t);
            }
            fputcsv($file, $tarr);
            unset($tarr);
        }
        unset($list);
        fclose($file);
        exit();
    }


    /**
     *  csv导入
     * @param $csv_file
     * @return array
     */
    public static function input_csv($csv_file)
    {
        $result_arr = array();
        $i = 0;
        while ($data_line = fgetcsv($csv_file, 10000)) { //10000是表示可以处理多长的字符
            if ($i > 2) {
                $GLOBALS ['csv_key_name_arr'] = $data_line;//eval('return ' . iconv('gbk', 'utf-8', var_export($data_line, true)) . ';');
                foreach ($GLOBALS['csv_key_name_arr'] as $csv_key_num => $csv_key_name) {
                    $result_arr[$i][$csv_key_name] = $data_line[$csv_key_num];
                }
                $i++;
                continue;
            }
            $i++;
        }
        return $result_arr;
    }

    public static function load_input_csv($csv_file)
    {
        $result_arr = array();
        $i = 0;
        while ($data_line = fgetcsv($csv_file, 10000, ',')) { //10000是表示可以处理多长的字符
            if ($i >= 1) {
                $GLOBALS ['csv_key_name_arr'] = $data_line;//eval('return ' . iconv('gbk', 'utf-8', var_export($data_line, true)) . ';');
                foreach ($GLOBALS['csv_key_name_arr'] as $csv_key_num => $csv_key_name) {
                    //$result_arr[$i][$csv_key_name] = $data_line[$csv_key_num];
                    $csv_key_name = is_numeric($csv_key_name) ? $csv_key_name : $csv_key_name;
                    $csv_key_name = iconv("utf-8", "utf-8", $csv_key_name);

                    $result_arr[$i][$csv_key_name] = $data_line[$csv_key_num];
                }
                $i++;
                continue;
            }
            $i++;
        }
        return $result_arr;
    }

    /**
     * 系统扶持上传文件
     * @param $csv_file
     * @return array
     */
    public static function invest_input_csv($csv_file)
    {
        $result_arr = array();
        $i = 0;
        while ($data_line = fgetcsv($csv_file, 10000)) { //10000是表示可以处理多长的字符
            if ($i >0 ) {
                $GLOBALS ['csv_key_name_arr'] = $data_line;//eval('return ' . iconv('gbk', 'utf-8', var_export($data_line, true)) . ';');
                foreach ($GLOBALS['csv_key_name_arr'] as $csv_key_num => $csv_key_name) {
                    $result_arr[$i][$csv_key_name] = $data_line[$csv_key_num];
                }
                $i++;
                continue;
            }
            $i++;
        }
        return $result_arr;
    }
}