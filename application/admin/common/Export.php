<?php


namespace app\admin\common;


class Export
{
    public function exportToCsv($headerList = [] , $data = [] , $fileName = '' , $tmp = []){
        //文件名称转码
        $fileName = iconv('UTF-8', 'GBK', $fileName);
        //设置header头
//        header('Content-Type: application/vnd.ms-excel');
        header("Content-type:text/csv");
        header('Content-Disposition: attachment;filename=' . $fileName . '.csv');
        header('Cache-Control: max-age=0');
        //打开PHP文件句柄,php://output,表示直接输出到浏览器
        $fp = fopen("../public/excel/".$fileName.".csv","a");

//        $fp = fopen("php://output","a");
        //备用信息
//        foreach ($tmp as $key => $value) {
//            $tmp[$key] = iconv("UTF-8", 'GBK', $value);
//        }
//        //使用fputcsv将数据写入文件句柄
//        fputcsv($fp, $tmp);
        //输出Excel列表名称信息
        foreach ($headerList as $key => $value) {
            $headerList[$key] = iconv('UTF-8', 'GBK', $value);//CSV的EXCEL支持BGK编码，一定要转换，否则乱码
        }
        //使用fputcsv将数据写入文件句柄
        fputcsv($fp, $headerList);
        //计数器
        $num = 0;
        //每隔$limit行，刷新一下输出buffer,不要太大亦不要太小
        $limit = 100000;
        //逐行去除数据,不浪费内存
        $count = count($data);
        for($i = 0 ; $i < $count ; $i++){
            $num++;
            //刷新一下输出buffer，防止由于数据过多造成问题
            if($limit == $num){
                ob_flush();
                flush();
                $num = 0;
            }
            $row = $data[$i];
            foreach ($row as $key => $value) {
                $row[$key] = iconv('UTF-8', 'GBK', $value);
            }
             fputcsv($fp, $row);
        }
        fclose($fp);
    }


    /**
     * 导出数据到 CSV 文件
     * @param array $data  数据
     * @param array $title_arr 标题
     * @param string $file_name CSV文件名
     */
    function export_down_csv(&$data, $title_arr, $file_name = '') {
        ini_set("max_execution_time", "3600");
        $csv_data = '';
        /** 标题 */
        $nums = count($title_arr);
        for ($i = 0; $i < $nums - 1; ++$i) {
            $csv_data .= '"' . $title_arr[$i] . '",';
        }
        if ($nums > 0) {
            $csv_data .= '"' . $title_arr[$nums - 1] . "\"\r\n";
        }
        foreach ($data as $k => $row) {
            for ($i = 0; $i < $nums - 1; ++$i) {
                $row[$i] = str_replace("\"", "\"\"", $row[$i]);
                $csv_data .= '"' . $row[$i] . '",';
            }
            $csv_data .= '"' . $row[$nums - 1] . "\"\r\n";
            unset($data[$k]);
        }
        $csv_data = mb_convert_encoding($csv_data, "GBK", "UTF-8");
        $file_name = empty($file_name) ? date('Y-m-d-H-i-s', time()) : $file_name;
        if (strpos($_SERVER['HTTP_USER_AGENT'], "MSIE")) { // 解决IE浏览器输出中文名乱码的bug
            $file_name = urlencode($file_name);
            $file_name = str_replace('+', '%20', $file_name);
        }
        $file_name = $file_name . '.csv';
        header("Content-type:text/csv;");
        header("Content-Disposition:attachment;filename=" . $file_name);
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');
        var_dump($csv_data);
        echo $csv_data;
    }

}